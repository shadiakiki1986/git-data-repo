<?php

namespace GitDataRepo;

class TempFolderDataRepoTest extends \PHPUnit_Framework_TestCase
{

    public function testUnmocked()
    {
        $gdr = new TempFolderDataRepo("test");
        $gdr->remove("bla");
        $bla = $gdr->get("bla");
        $this->assertTrue(is_null($bla));
        $gdr->set("bla", "foo");
        $bla1 = $gdr->get("bla");
        $this->assertEquals($bla1, "foo");
    }

}
