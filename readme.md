## Lodge of Sorceresses - Event Planner (powered by Laravel 5)

[![Build Status](https://travis-ci.org/AudithSoftworks/LodgeOfSorceresses-Event-Planner.svg?branch=master)](https://travis-ci.org/AudithSoftworks/Basis)

[![](https://img.shields.io/docker/automated/audithsoftworks/basis.svg?maxAge=2592000?style=plastic)](https://microbadger.com/images/audithsoftworks/basis "Docker Hub public images")
[![](https://images.microbadger.com/badges/version/audithsoftworks/basis.svg)](https://microbadger.com/images/audithsoftworks/basis "Docker Hub public images")
[![](https://images.microbadger.com/badges/image/audithsoftworks/basis.svg)](https://microbadger.com/images/audithsoftworks/basis "Docker Hub public images layers")
[![](https://img.shields.io/docker/pulls/audithsoftworks/basis.svg)](https://microbadger.com/images/audithsoftworks/basis "Docker Hub public images")

This is a custom Event Planner built for MMO guild [Lodge of Sorceresses](https://lodgeofsorceresses.com), integrated with our Guild Forums (powered by IPSCommunity).

### Installation

#### Setting up your Developer Environment

I have included a build script in ```./storage/scripts/dev-env/build.sh``` inside of which you can see steps necessary to spin up desired Docker configuration and prepare your development environment. Steps involved are:

1. Build or pull necessary Docker containers.
2. Start your Docker-Compose configuration.
3. Create ```.env``` file, containing your environmental variables.
4. Switch into the primary container environment, to start building your environment (Note: before doing so, please read the important note in ```build.sh``` file!):
    1. Install Sauce Connect and start it as a daemon.
    2. Install NPM dependencies.
    3. Install ```woff-2``` and it's submodules; and build them (used to build custom web-fonts).
    4. Install ```css3-font-converter``` package and build it (used to build custom web-fonts).
    5. Clone/update ```google-fonts``` to local storage, copy required font files and build your web-fonts.
    6. Run Webpack to build web assets.
    7. Install Composer dependencies.
    8. Using Laravel Artisan, generate an encryption key and run migrations, install Laravel Passport keys.
    9. Since Docker runs with root privileges, ```chown``` all newly created files to your host machine UUID:GUID (assuming it is 1000:1000, modify if necessary).
    10. And finally, run all the tests.
