#!/bin/bash

nohup /opt/confd/bin/confd -interval 10 -node "http://${ETCD_NODE}:${ETCD_PORT}" -confdir /etc/confd >> /var/log/confd.log
service haproxy start
