# Deployments
A GitHub action will automatically deploy the master branch to prod when
a new version of this file is pushed. It uses [git-ftp](https://git-ftp.github.io/)
to only upload changed files within the www/ folder while ignoring configuration
files specified in .git-ftp-ignore. This is a work-around until GitHub provides a
way to manually trigger Actions.

By convention, the date of the push is added to the list below in PST.

## Pushes
* 2019/12/01 - 20:58