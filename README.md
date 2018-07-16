# GTM Behat Context

Provides step definitions to check GTM implementation on web pages

```
DennisDigital\Behat\Gtm\Context\GtmContext
```

## Installation

```
composer require dennisdigital/behat-gtm
```

## Step Definitions

```gherkin
Given google tag manager id is :id
Given google tag manager data layer setting :key should be :value
Given google tag manager data layer setting :key should match :value
```

> Note: use `@javascript` to check using JS
