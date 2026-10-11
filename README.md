# Meta plugin for CakePHP
[![CI](https://github.com/dereuromark/cakephp-meta/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-meta/actions/workflows/ci.yml?query=branch%3Amaster)
[![codecov](https://codecov.io/gh/dereuromark/cakephp-meta/branch/master/graph/badge.svg)](https://codecov.io/gh/dereuromark/cakephp-meta)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-meta/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-meta)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-meta/license.svg)](LICENSE)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-meta/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-meta)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

For older CakePHP branches see the [version map](https://github.com/dereuromark/cakephp-meta/wiki#cakephp-version-map).
Composer declares the current supported dependencies.

## What is this plugin for?
This plugin helps to maintain and output meta tags for your HTML pages, including SEO relevant parts like
"title", "keywords", "description", "robots" and "canonical".

It can be used as a simple view-only approach using the included helper, it can also be DB driven if desired, or dynamically
be created from the controller context by passing the meta data to the view.

## Installation and usage

Start with [Installation](https://dereuromark.github.io/cakephp-meta/guide/installation),
then follow [Getting started](https://dereuromark.github.io/cakephp-meta/guide/getting-started).
The [documentation website](https://dereuromark.github.io/cakephp-meta/) covers
configuration, social metadata, and JSON-LD.

The plugin provides a view helper. Database storage and admin editing belong to
the application; pass your own record values to the view as `_meta` data.
