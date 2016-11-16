# git-data-repo
PHP library to manage the storage of key-value pairs in a git repository.

Packagist: [![Latest Stable Version](https://poser.pugx.org/shadiakiki1986/git-data-repo/version)](https://packagist.org/packages/shadiakiki1986/git-data-repo)
[![Total Downloads](https://poser.pugx.org/shadiakiki1986/git-data-repo/downloads)](https://packagist.org/packages/shadiakiki1986/git-data-repo)
[![Latest Unstable Version](https://poser.pugx.org/shadiakiki1986/git-data-repo/v/unstable)](//packagist.org/packages/shadiakiki1986/git-data-repo)
[![License](https://poser.pugx.org/shadiakiki1986/git-data-repo/license)](https://packagist.org/packages/shadiakiki1986/git-data-repo)
[![Monthly Downloads](https://poser.pugx.org/shadiakiki1986/git-data-repo/d/monthly)](https://packagist.org/packages/shadiakiki1986/git-data-repo)
[![Daily Downloads](https://poser.pugx.org/shadiakiki1986/git-data-repo/d/daily)](https://packagist.org/packages/shadiakiki1986/git-data-repo)
[![composer.lock available](https://poser.pugx.org/shadiakiki1986/git-data-repo/composerlock)](https://packagist.org/packages/shadiakiki1986/git-data-repo)

Travis CI: [![Build Status](https://travis-ci.org/shadiakiki1986/git-data-repo.svg?branch=master)](http://travis-ci.org/shadiakiki1986/git-data-repo)


Code Climate: [![Code Climate](https://codeclimate.com/github/shadiakiki1986/git-data-repo/badges/gpa.svg)](https://codeclimate.com/github/shadiakiki1986/git-data-repo)
[![Test Coverage](https://codeclimate.com/github/shadiakiki1986/git-data-repo/badges/coverage.svg)](https://codeclimate.com/github/shadiakiki1986/git-data-repo/coverage)
[![Issue Count](https://codeclimate.com/github/shadiakiki1986/git-data-repo/badges/issue_count.svg)](https://codeclimate.com/github/shadiakiki1986/git-data-repo)

# Installing
`composer install`

# Usage
Check [tests/GitDataRepoTest.php](tests/GitDataRepoTest.php)

# Testing Using SSH keys
1. Create a repository on github/bitbucket/etc.
2. initialize it
```bash
mkdir /path/to/your/project
cd /path/to/your/project
git init
git remote add origin https://shadiakiki1986@bitbucket.org/shadiakiki1986/ffa-bdlreports-maps.git
```
2. make first commit
echo "shadiakiki1986" >> contributors.txt
git add contributors.txt
git commit -m 'Initial commit with contributors'
git push -u origin master
```
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

# Note on bitbucket
For bitbucket repos, one needs
```bash
git config --global user.email "you@example.com"
git config --global user.name "Your Name"
# Omit --global to set the identity only in this repository
```

# Developer notes

## travis with ssh keys
1. Follow section above on `testing using ssh keys`
3. Encrypt `~/.ssh/id_rsa` and `~/.ssh/id_rsa.pub` using [travis CLI](https://docs.travis-ci.com/user/encrypting-files/#Encrypting-multiple-files)
 * Note the need to tar the public and private keys because `travis encrypt-file` cannot encrypt more than one file
```bash
travis login # enter github credentials
tar cvf ssh_keys.tar -C ~/.ssh id_rsa id_rsa.pub
travis encrypt-file ssh_keys.tar --add
git add ssh_keys.tar.enc
```
References
* https://docs.travis-ci.com/user/encrypting-files/
* https://grosser.it/2014/03/01/allowing-travis-to-git-clone-by-adding-a-public-ssh-key/
* https://gist.github.com/lukewpatterson/4242707
* https://docs.travis-ci.com/user/private-dependencies/

## phpmd
`phpmd` issued warnings about static access to classes.
I disabled these warnings for the functions in subject.
To see them, check `grep SuppressWarnings * -r --exclude-dir=vendor`

## phpcs
If `phpcs` reports errors that can be fixed automatically, run `vendor/bin/phpcbf src/` and then commit the changes

## badges
Badges from [badge poser](https://poser.pugx.org/show/shadiakiki1986/git-data-repo#badges)

# TODO: trouble from chown and composer.json post-install cmd
I notice often that the chown in composer.json post-install is a problem when I use the code from web with www-data user or from shell in docker with root user. This brings up the need for a separate server that is running with its own "stable" filesystem and file permissions. These would at least be independent of developments in the `git-data-repo` library, as well as `composer install` or `composer update` or whatever.

Can this repo be changed to a php api for [korya/node-git-rest-api](https://github.com/korya/node-git-rest-api)?
Can the latter be dockerified?
Can it clone from github? How do credentials get passed?

If so,
* change name of repo from GitDataRepo to DataRepo
* rename class TempFolderDataRepo to TempFolder
* rename GitDataRepo class to something like GitLocal (this is the one that uses the locally installed git)
* add class GitRestApi that uses above server

