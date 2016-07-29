<?php

namespace GitDataRepo;

class GitDataRepo {
  function __construct($repo,$remote,$LOG_LEVEL=\Monolog\Logger::WARNING) {
    assert($repo instanceof \Coyl\Git\GitRepo);
    assert($repo->test_git());
    $this->repo = $repo;
    $this->remote = $remote;

    $this->log = new \Monolog\Logger('GitDataRepo');
    // http://stackoverflow.com/a/25787259/4126114
    $this->log->pushHandler(
      new \Monolog\Handler\StreamHandler(
        'php://stdout',
        $LOG_LEVEL)); // <<< uses a stream

    $this->log->debug("init");
    $this->log->debug("repo path: ".$repo->getRepoPath());
    $remoteHiddenPassword = preg_replace(
      "/http(s){0,1}:\/\/(.*):(.*)@(.*)/",
      "http$1://$2:****@$4",
      $remote);
    $this->log->debug("remote: ".$remoteHiddenPassword);
  }

  private function keyFullPath($key) {
    return $this->repo->getRepoPath()."/".$key;
  }

  private function pull() {
    $this->log->debug("pull step 1: push any stale data");
    $this->repo->run("push");

    $this->log->debug("pull step 2: pull from remote (using run)");
    #$this->repo->pull($this->remote,"master");
    $this->repo->run("pull");
  }

  public function get($key) {
    $this->pull();
    $fn = $this->keyFullPath($key);
    if(!file_exists($fn)) return null;
    return(file_get_contents($fn));
  }

  public function set($key,$data) {
    $this->pull();
    $fn = $this->keyFullPath($key);
    if(file_exists($fn)) {
      $existing = file_get_contents($fn);
      if($existing==$data) {
        $this->log->debug('data is same as in repo. Not overwriting.');
        return;
      }
    }
    file_put_contents(
      $fn,
      $data);

    $this->log->debug("add ".$key);
    $this->repo->add($key);

    $this->commitAndPush();
  }
  
  public function rm($key) {
    $this->pull();
    $fn = $this->keyFullPath($key);

    if(!file_exists($fn)) {
      $this->log->debug("rm key '".$key."' does not exist.");
      return;
    }

    $this->log->debug("rm ".$key);
    $this->repo->rm($key);

    $this->commitAndPush();
  }

  private function commitAndPush($msg="Committing from php") {
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

  /* repoPath: data repo path since using git-data-repo
   *           e.g. /var/cache/ffamfe_tcm
   * authFn: e.g. __DIR__."/../auth.json"
   * remote, e.g. https://bitbucket.org/shadiakiki1986/ffa-bdlreports-maps
   * loglevel=\Monolog\Logger::WARNING; // isset($argc)?\Monolog\Logger::DEBUG:\Monolog\Logger::WARNING;
   * gitconfig: e.g. array('user.email'=>'my@email.com','user.name'=>'my name')
  */
  public static function initGdrPersistentFromAuthJson($repoPath,$authFn,$remoteUrl,$loglevel,$gitconfig=array()) {
    // copied from accounting-bdlreports-mapeditor/action.php

    // check can put files here
    if(!is_writable($repoPath)) throw new \Exception("Cache folder '".$repoPath."' is not writable. You may need `[sudo] chown www-data:www-data -R ".$repoPath."`");

    // get remote credentials
    $remote=null;
    if(!file_exists($authFn)) throw new \Exception("File not found '".$authFn."'");
    $remote=json_decode(file_get_contents($authFn),true);
    $remote=$remote["http-basic"]["bitbucket.org"];
    $remote=GitDataRepo::injectRemoteCredentials($remoteUrl,$remote["username"],$remote["password"]);

    # copied from https://github.com/coyl/git/blob/master/src/Coyl/Git/GitRepo.php#L43
    $isgit=is_dir($repoPath) && file_exists($repoPath . "/.git") && is_dir($repoPath . "/.git");
    if (!$isgit) {
      $gr = \Coyl\Git\GitRepo::create($repoPath,$remote);

      # run some git config if needed
      foreach($gitconfig as $k=>$v) {
        $cmd = "config ".$k." '".$v."'";
        $gr->run($cmd);
      }

    } else {
      $gr = new \Coyl\Git\GitRepo($repoPath,false,false);
    }

    return new \GitDataRepo\GitDataRepo($gr,$remote,$loglevel);
  }

  static function injectRemoteCredentials($url,$username,$password) {
    $re = "/http(s){0,1}:\/\/([^:@]*)/";
    if(!preg_match($re,$url)) throw new \Exception("Invalid URL format: ".$url);
    $remote = preg_replace(
     $re,
      "http$1://".$username.":".$password."@$2",
      $url);
    return $remote;
  }
 
}
