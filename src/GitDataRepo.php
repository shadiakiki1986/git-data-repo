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

    $this->log->info("init");
    $this->log->info("repo path: ".$repo->getRepoPath());
    $this->log->info("remote: ".$remote);

  }

  function keyFullPath($key) {
    return $this->repo->getRepoPath()."/".$key;
  }

  function pull() {
    $this->log->info("pull from remote");
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

    $this->log->info("add ".$key);
    $this->repo->add($key);

    $this->log->info("Commit");
    $this->repo->commit("Committing from php");

    $this->push();
  }
  
  function push() {
    $this->pull();
    $this->log->info("Push");
    $this->repo->push($this->remote,"master");
  }

}
