# HackForge - Symfony Event Planner

## Description

HackForge is a comprehensive event management platform built with Symfony. It provides tools for creating events, managing attendees, sending notifications, and analyzing event data. The application features role-based access control, customizable event templates, and real-time updates. This project was developed as part of the coursework for PIDEV 3A at [Esprit School of Engineering](https://esprit.tn).

## Table of Contents

- [HackForge - Symfony Event Planner](#hackforge---symfony-event-planner)
  - [Description](#description)
  - [Table of Contents](#table-of-contents)
  - [Tech stack](#tech-stack)
    - [Front End](#front-end)
    - [Back End](#back-end)
  - [Installation](#installation)
    - [Prerequisites](#prerequisites)
    - [Setup Steps](#setup-steps)
  - [Configuration](#configuration)
  - [Usage](#usage)
  - [Contributions](#contributions)
  - [Acknowledgments](#acknowledgments)

## Tech stack

### Front End

This project's front end was built using html, twig, css and JS.

### Back End

This project's back end was built using symfony, a php framework.

## Installation

### Prerequisites

- PHP 8.2+
- Composer 2.5+
- Symfony CLI
- MySQL 8.0+

### Setup Steps

```bash
git clone https://github.com/YassineJanfaoui/EventPlanner-3A1-Web.git
cd EventPlanner-3A1-Web

# Install dependencies
composer install
```

## Configuration

Configure database settings in .env

```ini
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/hackforge?serverVersion=8.0"
```

## Usage

```bash
symfony serve
```

## Contributions

We thank these people for contributing to the project by implementing different modules

- [Mohamed Yassine Janfaoui](mailto:mohamedyassine.janfaoui@esprit.tn) : Bill and equipment modules
- [Ayoub Rebhi](mailto:ayoub.rebhi@esprit.tn) : User module
- [Molka Boukhris](mailto:molka.boukhris@esprit.tn) : Venue and Catering modules
- [Mohamed Ali Mraihi](mailto:medali.mraihi@esprit.tn) : Feedback and Teams modules
- [Semah Ayachi](mailto:semah.ayachi@esprit.tn) : Event module
- [Mahmoud Ben Khelil](mailto:mahmoud.benkhelil@esprit.tn) : Partner and Workshop modules

## Acknowledgments

This project was completed under the guidance of [Professor Mohamed Amine Hechmi](mailto:mohamedamine.hechmi@esprit.tn) at [**Esprit School of Engineering**](https://esprit.tn).
