# git-data-repo
PHP library to manage the storage of key-value pairs in a git repository.

[![Build Status](https://travis-ci.org/shadiakiki1986/git-data-repo.svg?branch=master)](http://travis-ci.org/shadiakiki1986/git-data-repo)

[packagist](https://packagist.org/packages/shadiakiki1986/git-data-repo)

# Installing
`composer install`

# Usage
Check [tests/GitDataRepoTest.php](tests/GitDataRepoTest.php)

# Testing Using SSH keys
1. Create a repository on github
2. Generate ssh keys: `ssh-keygen -t rsa -b 4096 -C "shadiakiki1986@gmail.com"`
3. for travis, encrypt env vars for `~/.ssh/id_rsa` and `~/.ssh/id_rsa.pub` into .travis.yml and copy them to `~/.ssh` in .travis.yml
```bash
travis login
travis encrypt-file ~/.ssh/id_rsa.pub --add
travis encrypt-file ~/.ssh/id_rsa     --add
```
3. Configure ssh keys
4. Copy private key to deploy keys setting of repository on github
6. Run tests
```bash
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_rsa
cat ~/.ssh/id_rsa.pub
export GITDATAREPO_REMOTE=git@github.com:shadiakiki1986/git-data-repo-testDataRepo
composer run-script test
```

References
* https://docs.travis-ci.com/user/encrypting-files/
* https://grosser.it/2014/03/01/allowing-travis-to-git-clone-by-adding-a-public-ssh-key/
* https://gist.github.com/lukewpatterson/4242707
* https://docs.travis-ci.com/user/private-dependencies/

# Testing Using github credentials
1. Create a repository on github
2. Run tests
```bash
export GITDATAREPO_REMOTE=https://shadiakiki1986:veggiepizza@github.com/shadiakiki1986/git-data-repo-testDataRepo
composer run-script test
```
