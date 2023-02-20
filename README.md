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

- [Docker Engine](https://docs.docker.com/compose/install/) and [Docker Compose](https://docs.docker.com/compose/install/), optionally [Docker Desktop](https://www.docker.com/products/docker-desktop/), if you prefer a GUI over the command-line.
- A free Twilio account. If you are new to Twilio, [click here to create a free account](http://www.twilio.com/referral/QlBtVJ)
- A free SendGrid account. If you are new to SendGrid, [click here to create a free account](https://signup.sendgrid.com/)  

## Usage

To use the project, first clone it locally, then change into the cloned directory by running the following commands:

```bash
git clone git@github.com:settermjd/slim-doctrine-quote-sender.git
cd slim-doctrine-quote-sender
```

After that, copy _.env.local_ as *.env* and set the five required environment variables in _.env_.
These are 

| Environment Variable      | Description                                                                                                                                                                                                     |
|---------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `SENDGRID_API_KEY`        | This is your SendGrid API key, required to make authenticated requests against SendGrid's APIs.                                                                                                                 |
| `SEND_FROM_EMAIL_ADDRESS` | This is the email address which emails will be sent from. The sender must be [authenticated](https://docs.sendgrid.com/glossary/sender-authentication).                                                         |
| `TWILIO_PHONE_NUMBER`     | This is the Twilio phone number which SMS will be sent from.                                                                                                                                                    |
| `TWILIO_ACCOUNT_SID`      | This is your Twilio Account SID (or username) required to make authenticated requests against Twilio's APIs. This is available in the **Project Info** tab of [the Twilio Console](https://twilio.com/console). |
| `TWILIO_AUTH_TOKEN`       | This is your Twilio Auth Token (or password) required to make authenticated requests against Twilio's APIs. This is available in the **Project Info** tab of [the Twilio Console](https://twilio.com/console).  |                                                                 |

Then, start the application by running the following command:

```bash
docker compose up -d
```

### Start sending developer quotes to users

To send quotes to both mobile and email users, call _bin/cli_ on a regular basis using an automation service, such as [Cron](https://en.wikipedia.org/wiki/Cron). 
For example, if you were using Cron, add the following to the relevant user's [Crontab](https://www.adminschoice.com/crontab-quick-reference) entry.

```bash
# Add an entry to send quotes to email users every Monday to Friday at 8am.
0 8 * * 1-5 bin/cli daily-developer-quotes:email-users >/dev/null 2>&1

# Add an entry to send quotes to mobile users every Monday to Friday at 8am.
0 8 * * 1-5 bin/cli daily-developer-quotes:mobile-users >/dev/null 2>&1
```

[Use Crontab Generator](https://crontab-generator.org/) to save time generating a Crontab entry.

## Further Reading

- [Our Request to your Webhook URL (Twilio)](https://www.twilio.com/docs/messaging/guides/webhook-request)
- [TwiML™ for Programmable SMS](https://www.twilio.com/docs/messaging/twiml#twilios-request-to-your-application)