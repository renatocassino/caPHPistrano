Developing yet
======================================================

Introduction
-------------
Library to make deploy apps written in PHP. It's based on Capistrano in Ruby.

Instalation
=============

Using Composer (recommended)
-------------

The recommended way to get a working copy of this project is to clone the repository
and use `composer` to install dependencies adding these lines in composer.json:

```
{
	"require": {
		"tokenpost/caphpistrano": "dev-master"
		...
	}
}
```

Getting started
-------------

The CaPHPistrano is very similar to Capistrano in ruby. We need to run this command to create our files to deploy.

```
vendor/bin/caphp install
```

This command will be create these files:
```
./deploy.php
./config/deploy
   ├─ default.ini
   └─ environments
      ├─ development.ini
      └─ production.ini
```

In these ini files, exists the configurations to ssh, git and composer in remote server.
In the `default.ini` file, you must insert the information that will be common in all files and in environments files you must be insert the informations for each environment.

This is the `default.ini` file.

```ini
;Application []:
application = name of your application

[git]
repository = https://user:pass@github.com/user/application.git ; Repository with name and pass in format https.
branch = master ; Name of branch

[ssh]
; The deploy only works with ssh
deploy_to = /var/www/website.com ; remote folder
user = root                      ; username remote
use_sudo = false                 ; If you need to call sudo command
password = mypassword            ; password remote (optional)
port = 22                        ; port (default=22)
server = website.com             ; server name
keep_releases = 5                ; Quantity of releases that you want to keep in remote server

[composer]
use = true                       ; If you want to use composer (default = true)
command = /var/www/composer.phar ; If you put the composer.phar in user/local/bin/composer.phar you can run only composer.phar, some else, /path/of/composer.phar
installOnDeploy = true           ; If the script cannot find the composer, you want to try to install automatically ?

[shared]
; Shared folders and files. Is different off capistranorb. Here, if the file isn't exists, the caphpistrano copy the files in the shared folder
files[] = config/autoload/doctrine_orm.local.php ; array with files
files[] = config/autoload/zendconfig.local.php   ; array with files
dir[] = data/uploads                             ; array with folders
dir[] = data/cache                               ; array with folders

[writable]
; folders and files with 777 permissions
dir[] = public/cache      ; dir array
dir[] = data/cache        ; dir array
dir[] = data/cache/thumbs ; dir array
```

After this you can run these commands:

```bash
	$ vendor/bin/caphp install
	$ vendor/bin/caphp create:environment
	$ vendor/bin/caphp list environments
	$ vendor/bin/caphp list tasks
	$ vendor/bin/caphp deploy:setup
	$ vendor/bin/caphp deploy:check
	$ vendor/bin/caphp deploy
	$ vendor/bin/caphp deploy:cold
	$ vendor/bin/caphp deploy:rollback
```

Creating the first deploy
--------------

Now, you can make a simple deploy in your application. First you need to run this command:

```
$ vendor/bin/caphp deploy:setup
```

This command will generate two folders `releases` and `shared`.
Now you can run this command:

```
$ vendor/bin/caphp deploy
```

This command will be create a new folder with your application in releases, and will create a symlink called `current`.


Creating your own tasks
--------------

After you change the values in the ini file, you can start to write your own tasks.
Create a new file `(example: deploy.php)` and insert this code.

```php
$caphp = new TokenPost\CaPHPistrano;

# Command to create a new task.
$caphp->task('commandTask','Description of this task', function() use (&$caphp) {
	$caphp->writeln('<comment>Hello</comment>, i\'m a task...');
});
$caphp->start();
```

And run in terminal:
```
$ vendor/bin/caphp deploy.php commandTask
Hello, i'm a task...
```

If you want to make a sequence of tasks, you can pass in third param an array with the names of the tasks.
Ex:

```php
$caphp->task('sequence','Sequence of tasks',['task1','task2','task3']);
```

If you want to add a task after another task, you can add this line.

```php
$caphp->task('myTaskAfterDeploy','This is my task after deploy.',function() {
	$this->writeln('This will be run after another task.');
});

// The second param, must be an array with the name of tasks.
$caphp->after('deploy',['myTaskAfterDeploy']);
```

If you want to add a task before, you can use the method before.

```php
// The second param, must be an array with the name of tasks.
$caphp->after('deploy',['myTaskBeforeDeploy','anotherTask']);
```

If you run the `deploy` the sequence will be `myTaskBeforeDeploy`, `anotherTask`, `deploy`, `myTaskAfterDeploy` when ran `$ vendor/bin/caphp deploy`.

Report Bugs
-------------

If you find any bugs, please send an email to renatocassino@gmail.com.
