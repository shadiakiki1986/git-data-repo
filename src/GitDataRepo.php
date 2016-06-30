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

  static function injectRemoteCredentials($url,$username,$password) {
    $re = "/http(s){0,1}:\/\/([^:@]*)/";
    if(!preg_match($re,$url)) throw new \Exception("Invalid URL format: ".$url);
    $remote = "https://".$username.":".$password."@github.com/shadiakiki1986/git-data-repo-testDataRepo";
    $remote = preg_replace(
      $re,
      "http$1://".$username.":".$password."@$2",
      $url);
    return $remote;
  }

  function keyFullPath($key) {
    return $this->repo->getRepoPath()."/".$key;
  }

  function pull() {
    $this->log->debug("pull from remote");
    $this->repo->pull($this->remote,"master");
  }

  function get($key) {
    $this->pull();
    $fn = $this->keyFullPath($key);
    if(!file_exists($fn)) return null;
    return(file_get_contents($fn));
  }

  function set($key,$data) {
    $this->pull();
    $fn = $this->keyFullPath($key);
    file_put_contents(
      $fn,
      $data);

    $this->log->debug("add ".$key);
    $this->repo->add($key);

    $this->commitAndPush();
  }
  
  function rm($key) {
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

  function commitAndPush($msg="Committing from php") {
    $this->log->debug("Commit");
    $this->repo->commit($msg);
    # $this->pull();
    $this->log->debug("Push");
    $this->repo->push($this->remote,"master");
  }

}
