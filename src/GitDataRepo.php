<?php

namespace GitDataRepo;

class GitDataRepo
{
    public function __construct($repo, $remote, $logLevel = \Monolog\Logger::WARNING)
    {
        assert($repo instanceof \Coyl\Git\GitRepo);
        assert($repo->test_git());
        $this->repo = $repo;
        $this->remote = $remote;

        $this->log = new \Monolog\Logger('GitDataRepo');
        // http://stackoverflow.com/a/25787259/4126114
        $this->log->pushHandler(
            new \Monolog\Handler\StreamHandler(
                'php://stdout',
                $logLevel
            )
        ); // <<< uses a stream

        $this->log->debug("init");
        $this->log->debug("repo path: ".$repo->getRepoPath());
        $remoteHiddenPassword = preg_replace(
            "/http(s){0,1}:\/\/(.*):(.*)@(.*)/",
            "http$1://$2:****@$4",
            $remote
        );
        $this->log->debug("remote: ".$remoteHiddenPassword);
    }

    private function keyFullPath($key)
    {
        return $this->repo->getRepoPath()."/".$key;
    }

    private function pull()
    {
        $this->log->debug("pull step 1/2: pull from remote (using run)");
        #$this->repo->pull($this->remote,"master");
        $this->repo->run("pull");

        # Probably no need for this since the change of the pull call above to run(pull).
        # Also, cannot have this push for read-only access.
        # $this->log->debug("pull step 2/2: push any stale data");
        # $this->repo->run("push");
    }

    public function get($key)
    {
        $this->pull();
        $key2 = $this->keyFullPath($key);
        if (!file_exists($key2)) {
            return null;
        }
        return(file_get_contents($key2));
    }

    // data: item passable to file_put_contents(key,data)
    public function set($key, $data)
    {
        $this->pull();
        $key2 = $this->keyFullPath($key);
        if (file_exists($key2)) {
            $existing = file_get_contents($key2);
            if ($existing==$data) {
                $this->log->debug('data is same as in repo. Not overwriting.');
                return;
            }
        }
        file_put_contents(
            $key2,
            $data
        );

        $this->log->debug("add ".$key);
        $this->repo->add($key);

        $this->commitAndPush();
    }
  
    public function remove($key)
    {
        $this->pull();

        if (!file_exists($this->keyFullPath($key))) {
            $this->log->debug("remove key '".$key."' does not exist.");
            return;
        }

        $this->log->debug("remove ".$key);
        $this->repo->rm($key);

        $this->commitAndPush();
    }

    private function commitAndPush($msg = "Committing from php")
    {
        $this->log->debug("Commit");
        $this->repo->commit($msg);
        # $this->pull();
        $this->log->debug("Push");
        // I don't understand the push function below
        // https://github.com/coyl/git/blob/master/src/Coyl/Git/GitRepo.php#L595
        // After the push, I would always be left with a git status `your repo is ahead of master`
        // If I just `git pull`, then the msg goes away.
        // Anyway, I'm solving it with the run call below
        //$this->repo->push($this->remote,"master");
        $this->repo->run("push");
    }

  /**
   * repoPath: data repo path since using git-data-repo
   *           e.g. /var/cache/ffamfe_tcm
   * authFn: e.g. __DIR__."/../auth.json", false for no credentials, e.g. read-only usage of public repo
   * remote, e.g. https://bitbucket.org/shadiakiki1986/ffa-bdlreports-maps
   * loglevel=\Monolog\Logger::WARNING; // isset($argc)?\Monolog\Logger::DEBUG:\Monolog\Logger::WARNING;
   * gitconfig: e.g. array('user.email'=>'my@email.com','user.name'=>'my name')
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
  */
    public static function initGdrPersistentFromAuthJson($repoPath, $authFn, $remoteUrl, $loglevel = \Monolog\Logger::WARNING, $gitconfig = array())
    {
        // copied from accounting-bdlreports-mapeditor/action.php

        // check can put files here
        if (!is_writable($repoPath)) {
            throw new \Exception("Cache folder '".$repoPath."' is not writable. You may need `[sudo] chown www-data:www-data -R ".$repoPath."`");
        }

        // get remote credentials
        $remote=null;
        if ($authFn) {
            if (!file_exists($authFn)) {
                throw new \Exception("File not found '".$authFn."'");
            }
            $remote=json_decode(file_get_contents($authFn), true);
            $remote=$remote["http-basic"]["bitbucket.org"];
            $remote=GitDataRepo::injectRemoteCredentials($remoteUrl, $remote["username"], $remote["password"]);
        } else {
            $remote = $remoteUrl;
        }

        # copied from https://github.com/coyl/git/blob/master/src/Coyl/Git/GitRepo.php#L43
        $isgit=is_dir($repoPath) && file_exists($repoPath . "/.git") && is_dir($repoPath . "/.git");
        if ($isgit) {
            $gitRepo = new \Coyl\Git\GitRepo($repoPath, false, false);
            return new \GitDataRepo\GitDataRepo($gitRepo, $remote, $loglevel);
        }

        # case of new git repo
        $gitRepo = \Coyl\Git\GitRepo::create($repoPath, $remote);
        # run some git config if needed
        foreach ($gitconfig as $k => $v) {
            $cmd = "config ".$k." '".$v."'";
            $gitRepo->run($cmd);
        }

        return new \GitDataRepo\GitDataRepo($gitRepo, $remote, $loglevel);
    }

    public static function injectRemoteCredentials($url, $username, $password)
    {
        $regExp = "/http(s){0,1}:\/\/([^:@]*)/";
        if (!preg_match($regExp, $url)) {
            throw new \Exception("Invalid URL format: ".$url);
        }
        $remote = preg_replace(
            $regExp,
            "http$1://".$username.":".$password."@$2",
            $url
        );
        return $remote;
    }

    // get the latest full commit hash
    public function version()
    {
        $this->pull();
        return $this->repo->logFormatted("%H", "", 1); // git log -1 --pretty=format:%H
    }

    /*
     * Get the latest commit date
     * returns a DateTime object
     *
     * Dev notes:
     * Using %ci instead of %ct (unix timestamp) to get timezone
     *
     * Example UNIX timestamp case
     * > git log -1 --pretty=format:%ct # outputs 1474354919
     * > php -r 'var_dump(\DateTime::createFromFormat("U",1474354919,new \DateTimeZone("Asia/Beirut"))->format("Y-m-d H:i:s O"));'
     *
     */
    public function date()
    {
        $this->pull();
        $dd = $this->repo->logFormatted("%ci", "", 1);
        return \DateTime::createFromFormat("Y-m-d H:i:s e",$dd);
    }

}
