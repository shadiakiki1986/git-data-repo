<?php

namespace GitDataRepo;

class GitDataRepoTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
      $fn = "/home/shadi/.mygithubauthjson";
      $creds = json_decode(file_get_contents($fn),true);
      # $creds = $creds["http-basic"]["bitbucket.org"];
      assert(
        array_key_exists("username",$creds) &&
        array_key_exists("password",$creds));
      $remote = "https://".$creds["username"].":".$creds["password"]."@github.com/shadiakiki1986/git-data-repo-testDataRepo";

      $repo1 = tempnam("/tmp","test");
      unlink($repo1);
      $gr1 = \Coyl\Git\GitRepo::create($repo1,$remote);
      $this->gdr1 = new GitDataRepo($gr1,$remote,\Monolog\Logger::DEBUG);

      $repo2 = "/home/shadi/Development/git-data-repo-testDataRepo";
      $gr2 = new \Coyl\Git\GitRepo($repo2,false,false);
      $this->gdr2 = new GitDataRepo($gr2,$remote,\Monolog\Logger::DEBUG);
    }

    public function testGet() {
#      $bla = $this->gdr2->get("bla");
#      $this->assertTrue(is_null($bla));

      $this->gdr2->set("bla","foo");
      $bla2 = $this->gdr2->get("bla");
      $this->assertEquals($bla2,"foo");

#$this->gdr2->push();

      $bla1 = $this->gdr1->get("bla");
      $this->assertEquals($bla1,"foo");

    }

}

