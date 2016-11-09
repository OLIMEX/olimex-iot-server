
**olimex-iot-server** is our attempt to create an easy-to-use Open-source software and Open-source hardware low cost do-it-yourself internet-of-things platform. The main goal is to use well known technologies. This allows extending and integration to be easy achievable.

## Technology

**OlimexIoT** is based on exchanging asynchronous JSON Messages between [IoT Nodes](https://iot.olimex.com/help/glossary.html#node) and [IoT Server](https://iot.olimex.com/help/glossary.html#server) through WebSockets or HTTP POST requests.

**WebSockets** allows bi-directional communication between the node and the server and is easy (by design) to pass through NAT Firewalls and Proxy Servers.

**HTTP POST requests** can be used in the case there is no need of bi-directional communication.

## OlimexIoT conceptual schemes

### Using our free **OlimexIoT Service**

Each **IoT Node** builds separate connection to **OlimexIoT Service**. Node's firmware have to be build with SSL support. Client connects to the service to monitor the nodes. Direct connection to the nodes from outside is not possible.

![](https://iot.olimex.com/help/images/OlimexIoT-01.jpg)

| Pros | Cons |
|------|------|
| <ul><li>Quick start</li><li>Easy to setup</li><li>You don't need to maintain own server</li></ul> | <ul><li>Requires constant internet connection</li><li><b>Your private</b> data is on <b>our public</b> server&nbsp;- if you are paranoid ![](https://iot.olimex.com/help/images/wink.png)</li></ul> |

See [How to use our free **OlimexIoT Service**](https://iot.olimex.com/help/service.html)

### Build your own **OlimexIoT Server**

Direct connection between **IoT Nodes** and **IoT Server** via existing wireless network. Firmware SSL support is not required. Possible scenarios for clients:

| Direct IoT Server connection | VPN Tunnel |
|------------------------------|------------|
| Client have to be able to connect to the server so firewall have to be configured to allow outside connections to the server. You will need: | If you have VPN to your place then there is no need of SSL certificate. |
| <ul><li>Static IP address</li><li>Fully Qualified Domain Name (FQDN)</li><li>SSL certificate for the server</li></ul> | <ol><li>Connect your device to your place VPN. This will provide secure encrypted connection.</li><li>Use internal IP address of the server to connect.</li></ol> |
| Direct connection to the nodes from outside is not possible. | This is the only scenario which allows direct connection to the nodes from outside. |

![](https://iot.olimex.com/help/images/OlimexIoT-02.jpg)

| Pros | Cons |
|------|------|
| <ul><li>Independent</li><li>Customizable to fit your exact needs</li></ul> | <ul><li>Steep learning curve</li><li>You should know what are you doing</li></ul> |

See [Build your own **OlimexIoT Server**](https://iot.olimex.com/help/server/index.html)
