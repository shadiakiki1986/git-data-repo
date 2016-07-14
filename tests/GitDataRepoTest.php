<?php

namespace GitDataRepo;

class GitDataRepoTest extends \PHPUnit_Framework_TestCase {

    public function testUnmocked() {
      if(!getenv("GITDATAREPO_REMOTE")) $this->markTestSkipped("Please define env var GITDATAREPO_REMOTE");
      $remote = getenv("GITDATAREPO_REMOTE");

      $repo1 = tempnam("/tmp","test");
      unlink($repo1);
      $gr1 = \Coyl\Git\GitRepo::create($repo1,$remote);
      $this->gdr1 = new GitDataRepo($gr1,$remote,\Monolog\Logger::WARNING);

      $repo2 = tempnam("/tmp","test");
      unlink($repo2);
      $gr2 = \Coyl\Git\GitRepo::create($repo2,$remote);
      $this->gdr2 = new GitDataRepo($gr2,$remote,\Monolog\Logger::WARNING);

      // $repo3 = "/home/shadi/Development/git-data-repo-testDataRepo";

      $this->gdr1->rm("bla");

      $bla = $this->gdr1->get("bla");
      $this->assertTrue(is_null($bla));

      $this->gdr1->set("bla","foo");
      $bla1 = $this->gdr1->get("bla");
      $this->assertEquals($bla1,"foo");

#$this->gdr1->push();

      $bla2 = $this->gdr2->get("bla");
      $this->assertEquals($bla2,"foo");
    }

    public function testInject() {
      $url = "https://github.com/shadiakiki1986/git-data-repo-testDataRepo";
      $username = "bla";
      $password = "bli";
      $expected = "https://bla:bli@github.com/shadiakiki1986/git-data-repo-testDataRepo";
      $actual = GitDataRepo::injectRemoteCredentials($url,$username,$password);
      $this->assertEquals($actual,$expected);
    }

    public function testSetTwice() {
      if(!getenv("GITDATAREPO_REMOTE")) $this->markTestSkipped("Please define env var GITDATAREPO_REMOTE");
      $remote = getenv("GITDATAREPO_REMOTE");

      $repo1 = tempnam("/tmp","test");
      unlink($repo1);
      $gr1 = \Coyl\Git\GitRepo::create($repo1,$remote);
      $this->gdr1 = new GitDataRepo($gr1,$remote,\Monolog\Logger::WARNING);

      $this->gdr1->rm("bla");
      $this->gdr1->set("bla","foo");
      $this->gdr1->set("bla","foo");
      $this->assertTrue(true);
    }
}
