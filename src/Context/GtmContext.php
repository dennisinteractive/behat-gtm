<?php

namespace DennisDigital\Behat\Gtm\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Class GtmContext.
 *
 * @package DennisDigital\Behat\Gtm\Context
 */
class GtmContext extends RawMinkContext {

  /**
   * Check the google tag manager present in the page.
   *
   * @Given google tag manager id is :arg1
   */
  public function tagManagerIdIs($id) {
    if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
      $this->assertSession()->responseContains("www.googletagmanager.com/gtm.js?id=$id");
    }
    else {
      $this->assertSession()->responseContains("www.googletagmanager.com/ns.html?id=$id");
    }
  }

  /**
   * Check google tag manager data layer contain key value pair.
   *
   * @Given google tag manager data layer setting :arg1 should be :arg2
   */
  public function dataLayerSettingShouldBe($key, $value) {
    $property_value = $this->getDataLayerValue($key);
    if ($value != $property_value) {
      throw new \Exception($value . ' is not the same as ' . $property_value);
    }
  }

  /**
   * Check google tag manager data layer contain key value pair.
   *
   * @Given google tag manager data layer setting :arg1 should match :arg2
   */
  public function getDataLayerSettingShouldMatch($key, $regex) {
    $property_value = $this->getDataLayerValue($key);
    if (!preg_match($regex, $property_value)) {
      throw new \Exception($property_value . ' does not match ' . $regex);
    }
  }

  /**
   * Check google tag manager data layer contain event key value.
   *
   * @Given google tag manager data layer event :event should have the following values:
   *
   * Example:
   * Wildcards * and ? allowed, and also multilevel arrays.
   * Given google tag manager data layer event "sign_event" should have the following values:
   *  | eventAction        | eventCategory | eventLabel | ecommerce:purchase:actionField:revenue | ecommerce:purchase:actionField:id |
   *  | behat_gtm_campaign | firma         | F_web      | 120                                    | ?_web_*                           |
   */
  public function dataLayerEventShouldHavePropertyValue($event, TableNode $values) {
    $hash = $values->getHash();
    $values_array = $hash[0];
    $event_array = $this->getDatalayerEvent($event);
    foreach ($values_array as $property => $value) {
      $this->checkDatalayerProperty($event_array, $property, $value);
    }
  }

  /**
   * Check datalayer property value.
   *
   * @param array $event_array
   *   Event array.
   * @param string $property
   *   Property datalayer.
   * @param string $expected_value
   *   Value datalayer.
   *
   * @throws \Exception
   */
  public function checkDatalayerProperty(array $event_array, $property, $expected_value) {
    if (!isset($event_array['event'])) {
      throw new \Exception('Event not found.');
    }
    $event_name = $event_array['event'];

    $value = $event_array;
    $property_keys = explode(':', $property);
    foreach ($property_keys as $key) {
      if(!isset($value[$key])) {
        throw new \Exception('Property ' . $property . ' not found on event ' . $event_name);
      }
      $value = $value[$key];
    }

    if (!$this->wildcardMatch($expected_value, $value)) {
      throw new \Exception('Value ' . $expected_value . ' not found on event ' . $event_name . ', value of property ' . $property . ' is ' . $value);
    }
  }

  public function wildcardMatch($pattern, $subject) {
    $pattern = strtr($pattern, [
      '*' => '.*?', // 0 or more (lazy) - asterisk (*)
      '?' => '.', // 1 character - question mark (?)
    ]);
    return preg_match("/$pattern/", $subject);
  }

  /**
   * Obtain datalater event array from event name.
   *
   * @param string $event
   *   Event.
   *
   * @return mixed
   *   Event.
   *
   * @throws \Exception
   */
  protected function getDatalayerEvent($event) {
    $json_arr = $this->getDataLayerJson();

    // Loop through the array and return the data layer event.
    foreach ($json_arr as $json_item) {
      if (isset($json_item['event']) && $json_item['event'] == $event) {
        return $json_item;
      }
    }
    throw new \Exception('Event ' . $event . ' not found.');
  }

  /**
   * Get Google Tag Manager Data Layer value.
   *
   * @param string $key
   *   Datalayer key.
   *
   * @return mixed
   *   Datalayer value from key.
   *
   * @throws \Exception
   */
  protected function getDataLayerValue($key) {
    $json_arr = $this->getDataLayerJson();

    // Loop through the array and return the data layer value.
    foreach ($json_arr as $json_item) {
      if (isset($json_item[$key])) {
        return $json_item[$key];
      }
    }
    throw new \Exception($key . ' not found.');
  }

  /**
   * Get dataLayer variable JSON.
   */
  protected function getDataLayerJson() {
    if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
      $json_arr = $this->getSession()->getDriver()->evaluateScript('return dataLayer;');
    }
    else {
      $json_arr = json_decode($this->getDataLayerJsonFromSource(), TRUE);
    }

    // If it's not an array throw an exception.
    if (!is_array($json_arr)) {
      throw new \Exception('dataLayer variable is not an array.');
    }

    return $json_arr;
  }

  /**
   * Get dataLayer variable JSON from raw source.
   */
  protected function getDataLayerJsonFromSource() {
    // Get the html.
    $html = $this->getSession()->getPage()->getContent();

    // Get the dataLayer json and json_decode it.
    preg_match('~dataLayer\s*=\s*(.*?);</script>~', $html, $match);
    if (!isset($match[0])) {
      throw new \Exception('dataLayer variable not found in source.');
    }

    return $match[1];
  }

  /**
   * Get dataLayer variable JSON from raw source.
   */
  protected function getDataLayerJsonFromJS() {
    $json_arr = $this->getSession()->getDriver()->evaluateScript('return dataLayer;');

    if (empty($json_arr)) {
      throw new \Exception('dataLayer variable not set on page.');
    }

    return $json_arr;
  }

}
