### Auto deployment using git and webhooks plugin

#### Set up Automated Deployments From GitHub With Webhook

* Setup config in file `.env`
```dotenv
DEPLOY_ENABLE=true
DEPLOY_GITHUB_SECRET=<random-string>
```

* Make drploy token, run command
```shell
php artisan deploy:make-token
```
* Set up command on build, make `.deploy.yml` in root your source folder.

Example:
```yml
github-deploy:
  commands:
    - git pull
    - composer install --no-dev
    - php artisan migrate --force
```

* Add a repository webhook on GitHub

To add a webhook to an existing repository, navigate to the GitHub repository page and go to "Settings" > "Webhooks". Click on "Add webhook".

 - Payload URL — A custom domain that points to your server or your server's public IP, followed by
```
https://yourdomain.com/webhook/deploy/github/{action}/{token}
```

**{action}**: Action define in your file `.deploy.yml`, example above, the action will be `github-deploy`

**{token}**: Your token created above

 - Secret — A shared secret `DEPLOY_GITHUB_SECRET` in `.env`
 - Which events would you like to trigger this webhook? (Default option: "Just the push event.")

##### IF YOU WANT TO ADD CUSTOM PARAM TO YOUR COMMANDS ADD QUERY STRING TO WEBHOOK URL

Example:

Webhook Url:
```
https://yourdomain.com/webhook/deploy/github/{action}/{token}?theme=default
```

In file `.deploy.yml`
```yml
github-deploy:
  commands:
    - cd themes/{theme}
    - git pull
```
