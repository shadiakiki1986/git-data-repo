<?php
namespace GitDataRepo;

// This is a class similar to GitDataRepo but which only uses a folder in the temp folder
// Use this class if you don't care to have git versioning or git push to a git server, e.g. bitbucket or github
class TempFolderDataRepo
{
    public function __construct(string $name)
    {
        $this->root = sys_get_temp_dir()."/".$name;
        if (!is_dir($this->root)) {
            mkdir($this->root);
        }
    }
  
    public function set(string $key, string $value)
    {
        file_put_contents($this->key2path($key), $value);
    }
  
    private function key2path(string $key)
    {
        return $this->root."/".$key;
    }
  
    public function get(string $key)
    {
        $path = $this->key2path($key);
        if (!file_exists($path)) {
            return null;
        }
        return file_get_contents($path);
    }

    public function remove(string $key)
    {
        $path = $this->key2path($key);
        if (!file_exists($path)) {
            return null;
        }
        unlink($path);
    }
}
