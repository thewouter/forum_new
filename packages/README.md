# What is this?

_Looking for publishing/installation instructions? [See here](#installation-and-publishing)._

These packages are the beginning of a monorepo setup, containing as many of vanilla's published modules in 1 place.

Handling changes/pull requests thats span multiple repos is a real pain.
By using a monorepo most officially maintained modules are in the same repo.

This is quite taboo but let's look at the pros and cons:

**Pros:**

-   Single lint, build, test and release process.
-   Easy to coordinate changes across modules.
-   Single place to report issues.
-   Easier to setup a development environment.
-   Tests across modules are run together which finds bugs that touch multiple modules easier.

**Cons:**

-   Codebase looks more intimidating.
-   Repo is bigger in size.
-   [Can't `npm install` modules directly from GitHub](https://github.com/npm/npm/issues/2974)
-   ???

## This is dumb! Nobody in open source does this!

[Babel](https://github.com/babel/babel/blob/master/doc/design/monorepo.md), [React](https://github.com/facebook/react/tree/master/packages), [Meteor](https://github.com/meteor/meteor/tree/devel/packages), and [Ember](https://github.com/emberjs/ember.js/tree/master/packages), among others, do this.

## Previous discussion

-   [Babel](https://github.com/babel/babel/blob/master/doc/design/monorepo.md) _(The inspiration for this doc)_
-   [Dan Luu](http://danluu.com/monorepo/)
-   [Gregory](http://gregoryszorc.com/blog/2014/09/09/on-monolithic-repositories/)
-   [Szorc](http://gregoryszorc.com/blog/2015/02/17/lost-productivity-due-to-non-unified-repositories/)
-   [Face](https://www.youtube.com/watch?v=X0VH78ye4yY)[book](https://code.facebook.com/posts/218678814984400/scaling-mercurial-at-facebook/)
-   [Benjamin Pollack](http://bitquabit.com/post/unorthodocs-abandon-your-dvcs-and-return-to-sanity/)
-   [Benjamin Eberlei](https://qafoo.com/resources/presentations/froscon_2015/monorepos.html)
-   [Simon Stewart](http://blog.rocketpoweredjetpants.com/2015/04/monorepo-one-source-code-repository-to.html)
-   [Digital Ocean](https://www.digitalocean.com/company/blog/taming-your-go-dependencies/)
-   [Google](http://www.infoq.com/presentations/Development-at-Google), [another](https://www.youtube.com/watch?v=W71BTkUbdqE)
-   [Twitter](https://www.youtube.com/watch?v=bjh4DHuOf4E)
-   [thedufer](http://www.reddit.com/r/programming/comments/1unehr/scaling_mercurial_at_facebook/cek9nkq)
-   [Paul Hammant](http://paulhammant.com/categories.html#Trunk_Based_Development)
-   [Exponent](https://blog.getexponent.com/universe-exponents-code-base-f12fa236b8e#.9dj8a82be)

## Installation and Publishing

This directory represents the [@vanilla](https://www.npmjs.com/org/vanilla) npm org packages.

## Publishing

1. Ensure you have publish access on the org.
2. `yarn install`
3. `yarn publish-all`
