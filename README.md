
# Talenta Auto

Made with 💙 for Indonesian Employee who using Talenta by Mekari.

- Too lazy to clock-in or clock-out ?
- Or often forget to clock-in or clock-out  ?

No worries ! Talenta Auto is here.

## Requirement

Make sure your machine is installed below requirement

| Stack      | Version  |
|:-----------|:---------|
| `php`      | `8.*`    |
| `composer` | `2.*`    |
| `nano`     | `Latest` |

## Installation

Clone this repository.

```bash
$ git clone https://github.com/yuliusardian/talenta-auto.git && cd talenta-auto/src/
```

Composer install

```bash
$ composer install
```

## Setup

Copy .env.example to .env

```bash
$ cp -rv .env.example .env
```

To run this project, you will need to add/edit the following environment variables to your .env file


| Variable                            | Description                                                               |
|:------------------------------------|:--------------------------------------------------------------------------|
| `TALENTA_USER_EMAIL`                | Talenta email                                                             |
| `TALENTA_USER_PASSWORD`             | Talenta password                                                          |
| `TALENTA_LIVE_ATTENDANCE_LATITUDE`  | Talenta live attendance latitude                                          |
| `TALENTA_LIVE_ATTENDANCE_LONGITUDE` | Talenta live attendance longitude                                         |
| `TALENTA_LIVE_ATTENDANCE_SOURCE`    | Talenta live attendance source, possible value `mobileapp` or `mobileweb` |
| `TALENTA_CLOCK_IN_TIME`             | Talenta clock-in time, format `hh:mm`                                     |
| `TALENTA_CLOCK_OUT_TIME`            | Talenta clock-out time, format `hh:mm`                                    |
| `TALENTA_OFF_DAY`                   | Talenta off day, separator `,` example `saturday,sunday`                  |

After you add/edit the variable, final step is to set the command into `crontab` or `scheduler`

```bash
$ export VISUAL=nano; crontab -e
```

The config would be :

```bash
* * * * * cd /your-cloned-directory/talenta-auto/src && php artisan talenta:auto
```

That's it ! :)  If you love this project you can buy me a coffee.

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/yuliusardian)
Or
[saweria.co](https://saweria.co/yuliusardian)