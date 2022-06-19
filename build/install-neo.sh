#!/bin/bash

export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

wget http://dist.neo4j.org/neo4j-enterprise-4.0.0-unix.tar.gz > null
mkdir neo
tar xzf neo4j-enterprise-3.3.0-unix.tar.gz -C neo --strip-components=1 > null
sed -i.bak '0,/\(dbms\.security\.auth_enabled=\).*/s/^#//g' ./neo/conf/neo4j.conf
neo/bin/neo4j start > null &