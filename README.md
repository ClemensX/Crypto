# Crypto
Java implementation of various cryptographic algorithms

## Dependencies
We tried to keep dependencies to a minimum. This is what we need:
 * JSON implementation of JSR 374. We use javax.json. You should be able to easily change to whatever JSON processor you prefer.
 * JUnit 5 for test cases
 * log4j for logging

## Procedures

### Server Communication
You can use the low-level crypto classes to do whatever crypto functionality you want. But we also provide higher level functions to securely communicate with servers. If this fits your requirements you may use or adapt this for your own projects. Some things we do different from what you may be used to - you need to check if you agree with our mindset.

All communication is based on HTTP, not HTTPS. We do not believe in the security of HTTPS and found it more rewarding to accept an unprotected environment and build secure communication on top of it.

#### Key Agreement
Goal: Agree on a secret AES-256 session key that will be used to encrypt all further communication in this session.

| **Session Key Exchange**  | Server | Client |
| -------------             | ------ | ------ |
| Preparation (outside this protocol)              | has Client Public Key PUBc | has Server Public Key PUBs
| Initiate | | ECDH: prepare key exchange message|
|   | | ECDSA: Sign ECDH message with private key and append to message |
