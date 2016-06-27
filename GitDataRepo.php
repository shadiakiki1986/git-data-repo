<?php


class GitDataRepo {
  function __construct($cache,$repo=null) {
    assert($repo instanceof \Coyl\Git\GitRepo);
    assert($repo->test_git());
    var_dump("init",$repo->getRepoPath());
    $this->repo = $repo;
    $this->cache=$cache;
  }
    if(!is_null($git)) {
      assert(is_array($git));
      assert(
        array_key_exists("username",$git) &&
        array_key_exists("password",$git));
    }
    $this->git=$git;
  }

  function initialize() {
    if(!file_exists($this->cache)) throw new \Exception("Installation requires `mkdir ".$this->cache."`");
    if(!file_exists($this->repo)) $this->cloneRepo();
  }

  function map2fn($map) {
    return $this->repo."/".$map."_map.json";
  }

  function get($name) {
    $fn = $this->map2fn($name);
    if(!file_exists($fn)) {
      return array();
    }

    if($this->isGit()) {
      $cmd="cd ".$this->repo." && git pull";
      system($cmd);
    }

    return(json_decode(
      file_get_contents($fn),
      true));
  }

  function save($name,$map) {
    $fn = $this->map2fn($name);

    if($this->isGit()) {
      $cmd="cd ".$this->repo." && git pull";
      $res=0;
      system($cmd,$res);
      // if pulled stuff
      if($res==0) throw new \Exception("Will not save. Refresh then re-apply your edits and save");
    }

    file_put_contents(
      $fn,
      json_encode(
        $map,
        JSON_PRETTY_PRINT));

    if($this->isGit()) {
      $cmd="cd ".$this->repo." && git commit -am 'Committing from php' && git push";
      system($cmd);
    }
  }

  function isGit($full=true) {
    if(is_null($this->git)) return false;
    exec("which git",$out,$res);                                           
    if(!!$res) return false;                                               
    if(!$full && !file_exists($this->repo."/.git")) return false;
    return true;
  }

  function cloneRepo() {
    if(!$this->isGit(false)) {
      mkdir($this->repo);
      return;
    }

    $cmd="git clone https://".$this->git["username"].":".$this->git["password"]."@bitbucket.org/shadiakiki1986/ffa-bdlreports-maps ".$this->repo;
    system($cmd);
  }
}
