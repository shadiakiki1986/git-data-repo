<?php


$cache = tempnam("/tmp");
unlink($cache);
$gr = new \Coyl\Git\Git::cloneRemote($cache,
$gdr = new GitDataRepo();

    if(!is_null($git)) {
      assert(is_array($git));
      assert(
        array_key_exists("username",$git) &&
        array_key_exists("password",$git));
    }
    $this->git=$git;

