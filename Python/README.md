Accessing InLogg using Python
===========================


`vendor_sample.py` contains a sample execution flow through different APIs provided 
by InLogg to logistics vendors.

Following are some of the important steps explained along with relevant code

Authenticating user and getting token
--------------------------------------

This would generally be the first step in the flow of APIs. Upon successful 
authentication, a `token` and `id` is received, which needs to be used in all
future calls (unless the token expires).

```
