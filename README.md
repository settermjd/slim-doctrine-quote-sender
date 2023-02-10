# Developer Daily Quote Sender

This is a small project which a series of tutorials for the Twilio blog will be based.
The intention of the project is to show how to build a project using Slim Framework, Doctrine, and two Twilio services;

- SendGrid
- Twilio Messaging

## About

The application allows users to subscribe to receive daily developer quotes, such as "_Don't comment bad code - rewrite it._", by Brian Kernighan, by both SMS and email.
It's another take on the daily quote services that are pretty common place around the internet.

The application has a Docker Compose configuration to simplify getting it up and running as quickly as possible, and to help document the application's requirements.

## Prerequisites

To use this project, you will need to have the following:

- [Docker Engine](https://docs.docker.com/compose/install/) and [Docker Compose](https://docs.docker.com/compose/install/)

## Usage

To use the project, first clone it locally, then change into the cloned directory and start the application, by running the following commands:

```bash
git clone git@github.com:settermjd/slim-doctrine-quote-sender.git
cd slim-doctrine-quote-sender
docker compose up -d
```

## Further Reading

- [Our Request to your Webhook URL (Twilio)](https://www.twilio.com/docs/messaging/guides/webhook-request)
- [TwiML™ for Programmable SMS](https://www.twilio.com/docs/messaging/twiml#twilios-request-to-your-application)