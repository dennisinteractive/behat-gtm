# GTM Behat Context

Provides step definitions to check GTM implementation on web pages

## Installation

```
composer require dennisdigital/behat-gtm
```

### Configuration
Setup extension by specifying your behat.yml:

Example on how to add extension and configure items to be ignored.
```yaml
default:
  extensions:
    DennisDigital\Behat\Gtm\Context\GtmContext:
      ignoreGtmItems:
        - gtm.element
```

## Step Definitions

```gherkin
Given google tag manager id is :id
Given google tag manager data layer setting :key should be :value
Given google tag manager data layer setting :key should match :value
```

> Note: use `@javascript` to check using JS

## Using dot notation to get nested GTM elements
For the nested GTM elements like 
```
{
   "event": "productImpression",
   "timestamp": 1694581407435, 
   "eventLabel2": "97 items",
   "ecommerce": {
      "currencyCode": "KWD",
      "impressions": [
         {
            "name": "Knockout Front-Close Sports Bra",
            "id": "1119313554A2",
            "price": 23,
            "category": "Victorias Secret/Bras/Styles/Lightly Lined Bras",
            "variant": "",
            "product_style_code": "11193135",
            "dimension2": "configurable",
            "dimension3": "Regular Product",
            "dimension4": 4,
            "brand": "Victorias Secret",
            "list": "PLP|Victorias Secret|Apparel|All Apparel",
            "position": "1"
         },
      ]
   },
   "gtm.uniqueEventId": 127
}
```
To extract the GTM value nested in arrays i.e name under impressions. We can use dot notation
```
Given google tag manager data layer setting "ecommerce.impressions[0].name" should match "~Knockout~"
```