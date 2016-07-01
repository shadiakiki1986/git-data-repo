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
6. Copy public key to deploy keys setting of repository on github
5. Configure ssh keys
```bash
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_rsa
cat ~/.ssh/id_rsa.pub
```
7. Run tests
```bash
export GITDATAREPO_REMOTE=git@github.com:shadiakiki1986/git-data-repo-testDataRepo
composer run-script test
```

# Testing Using github credentials
1. Create a repository on github
2. Run tests
```bash
export GITDATAREPO_REMOTE=https://shadiakiki1986:veggiepizza@github.com/shadiakiki1986/git-data-repo-testDataRepo
composer run-script test
```

# Developer notes: travis with ssh keys
1. Follow section above on `testing using ssh keys`
3. Encrypt `~/.ssh/id_rsa` and `~/.ssh/id_rsa.pub` using [travis CLI](https://docs.travis-ci.com/user/encrypting-files/#Encrypting-multiple-files)
 * Note the need to tar the public and private keys because `travis encrypt-file` cannot encrypt more than one file
```bash
travis login # enter github credentials
tar cvf ssh_keys.tar ~/.ssh/id_rsa ~/.ssh/id_rsa.pub
travis encrypt-file ssh_keys.tar --add
git add ssh_keys.tar.enc
```
References
* https://docs.travis-ci.com/user/encrypting-files/
* https://grosser.it/2014/03/01/allowing-travis-to-git-clone-by-adding-a-public-ssh-key/
* https://gist.github.com/lukewpatterson/4242707
* https://docs.travis-ci.com/user/private-dependencies/


