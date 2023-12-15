# Docker PHP Launch Darkly example

This repository contains a simple single-page PHP Docker application to demonstrate Launch Darkly

## Requirements

The instructions assume that you have installed:

- [Docker](https://www.docker.com/)
- [Git](https://git-scm.com/)
- Command line terminal (e.g., bash/zsh)

### LaunchDarkly requirements

You will need:

- A project
  - You can create one from Account Settings -> Projects in the LaunchDarkly Dashboard
- SDK Key from the project settings in Account Settings -> Projects
  - Select your project, and copy the sdk key for the environment that you're going to use for this
- Two feature flags:
  - `extra_content_flag`
    - Simple true/false flag
  - `targeted_flag`
    - true/false flag that targets specific users

## How to use this repo

> Note: The commands below are from a UNIX shell. But, as long as you have Docker and Git installed, the same commands should run on Windows.

Ensure that docker is running, and both, git and docker command line tools are available. 

Clone the repo and build the Docker image:

```shell
git clone https://github.com/fvoges/docker-php-launchdarkly
docker build -t fvoges/docker-php-launchdarkly .
```

If the build runs fine, you should see output similar to this:

```shell
$ docker build -t fvoges/docker-php-launchdarkly .
[+] Building 1.0s (18/18) FINISHED                                                                                                                  docker:default
 => [internal] load .dockerignore                                                                                                                             0.0s
[...]
 => exporting to image                                                                                                                                        0.0s
 => => exporting layers                                                                                                                                       0.0s
 => => writing image sha256:caa475c2fa1ebf7804c16896f7d2592da5322178ca1d0696fe961f46ab6c1fa1                                                                  0.0s
 => => naming to docker.io/fvoges/docker-php-launchdarkly                                                                                                     0.0s

What's Next?
  View a summary of image vulnerabilities and recommendations → docker scout quickview
$
```

The `writing image` line near the bottom means that the image has been created.

You should be able to see the new image with the `docker image list` command:

```shell
$ docker image list
REPOSITORY                           TAG       IMAGE ID       CREATED          SIZE
fvoges/docker-php-launchdarkly       latest    caa475c2fa1e   12 minutes ago   508MB
[...]
$
```

### LaunchDarkly configuration - part 1

1. Go to your project and environment in the dashboard.
1. Go to Feature Flags
1. Create a new flag
1. On the first page of the dialog, select "Experiment"
1. Name the flag `extra_content_flag` and click next
1. At the top, select "Boolean" from the Flag Variations, and click Create flag (leave the variation names blank)
2. Go back to the Feature flags page
3. Create another flag following the same steps as before, but name it `targeted_flag`
4. On the Feature flag details page, edit the default rule, and change it to `false`
5. Since you haven't use the application yet, you will not have any context/users available. We're going to come back later to finish the configuration

### Run application

The application runs inside a container. The feature flag names are configured near the top of [web/index.php](web/index.php):

```php
$ld_sdk_key = $_ENV['LD_SDK_KEY'];
$targeted_flag_key = 'targeted_flag';
$extra_flag_key = 'extra_content_flag';
```

You shouldn't need to change the code. The application expects an environment variable called `LD_SDK_KEY` containing the LaunchDarkly SDK key. Assuming that the build was successful, you should be able to run the application with the following command:

```shell
docker run --rm -it \
  --name test \
  -p 8080:80 \
  -e LD_SDK_KEY=YOUR_SDK_KEY \
  fvoges/docker-php-launchdarkly
```

> NOTE: Replace `YOUR_SDK_KEY` in the example above with your actual LaunchDarkly SDK key


Then, you should be able to access the application pointing your browser to [http://localhost:8080/](http://localhost:8080/). 

> NOTE: This assumes that you're running Docker locally, if you're running docker on a different computer, then replace `localhost` with the appropriate IP address or DNS host name

### Creating users

The application shows a very simple page with a form to input a user name. Whatever value you submit, will create a new user context using that value as the context name and user name. For example, if you enter "Joe", and click the submit button, you will be "logged in" as the user "Joe". The application will accept any name (there's no input checking, so be careful).

Go ahead an login at least once, for example using "Joe" as the user name. Remember the user name, we're going to use it to finish setting up the targeted feature flag.

### LaunchDarkly configuration - part 2

After you've faked a login, you should see a new user context inside the "Contexts" page of your project's console. This can take a few minutes.

Now you can finish off the configuration:

1. Go to the "Feature flags" page and click on `targeted_flag`
1. Under "Quick start", select "Target Individuals"
1. That will add a "Targets" section
1. In the "Serve true" section, select "Search to find or add users" and type the name of a user you created in the previous section (e.g., Joe)
1. Click "Review and save" (top right), then "Save changes"

All the configuration is now done

## Using the feature flags

The setup is now complete, but the flags are set to false. To see them in action, go ahead and turn one on.

Depending on the flag you used, you will see different behaviors.

### Extra Content Flag

When on, this flag will add the following block of text at the bottom of the page:

> Some extra content controlled by a flag

To turn it on and off, just toggle the switch on Feature flags page.

Refreshing the application page should show/hide the extra content based on the flag status.

### Targeted flag

When on, this flag will add a greeting to specific users (the ones configured in the Target section).

If you are not logged in, or the user doesn't match the ones targeted, the greeting will not show up in the page.

Try enabling the feature, and then login with a targeted user. You should see the greeting.

Now login with a different user, or click Submit leaving the user name entry field empty. You should not see the greeting.

When this feature flag is off, then no one will see the greeting.


