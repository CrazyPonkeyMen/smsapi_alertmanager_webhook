smsapi_alertmanager_webhook

This is simple webhook to intergrate alertmanager with https://www.smsapi.pl/

You just need to provide login and password both in alertmanager webhook and $generate_pass variable.

Then upload file to server with php handler

Example alertmanager config:

    global:
      resolve_timeout: 5m
    
    route:
      group_by: ['alertname']
      group_wait: 10s
      group_interval: 10s
      repeat_interval: 4h
      receiver: 'webhook'
    receivers:
    - name: 'webhook'
      webhook_configs:
      - send_resolved: false
        url: https:/your.page/alertmanager_webhook.php
        http_config:
          basic_auth:
            username: yourlogin
            password: yourpass
    inhibit_rules:
      - source_match:
          severity: 'critical'
        target_match:
          severity: 'warning'
        equal: ['alertname', 'dev', 'instance']
