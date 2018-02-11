# atarax/confd-haproxy
FROM debian:8-slim

RUN apt-get update && \
	apt-get install --yes wget haproxy && \
	wget https://github.com/kelseyhightower/confd/releases/download/v0.15.0/confd-0.15.0-linux-amd64 && \
 	mkdir -p /opt/confd/bin && \
	mv confd-0.15.0-linux-amd64 /opt/confd/bin/confd && \
	chmod +x /opt/confd/bin/confd && \
	export PATH="$PATH:/opt/confd/bin" && \
	apt-get purge --yes wget && \
	apt-get --yes autoremove && \
	apt-get --yes clean	

COPY startup.sh /
RUN chmod +x /startup.sh

CMD /startup.sh
