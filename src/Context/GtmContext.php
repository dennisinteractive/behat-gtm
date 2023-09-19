<?php
namespace DennisDigital\Behat\Gtm\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Class GtmContext
 * @package DennisDigital\Behat\Gtm\Context
 */
class GtmContext extends RawMinkContext {
  /**
   * Check the google tag manager present in the page
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
   * Waits until the Datalayer object is updated and then checks if property is available.
   *
   * @Given I wait for the data layer setting :arg1
   */
  public function waitDataLayerSetting($key, $loops = 10) {
    $loop = 0;
    do {
      try {
        $loop++;
        $this->getDataLayerValue($key);
        return true;
      } catch (\Exception $e) {
        // Ommit the exception until we finish the loop.
      }
      sleep(1);
    } while ($loop < $loops);

    throw new \Exception("$key not found after waiting for $loops seconds.");
  }

  /**
   * Check google tag manager data layer contain key value pair
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
   * Check google tag manager data layer contain key value pair
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
   * Get Google Tag Manager Data Layer value
   *
   * @param $key
   * @return mixed
   * @throws \Exception
   */
    protected function getDataLayerValue($key)
    {
        $json_arr = $this->getDataLayerJson();
        // Loop through the array and return the data layer value
        foreach ($json_arr as $json_item) {
            // Check if the key contains dot.
            if (strpos($key, '.', 0) !== false) {
                if (strpos($key, '[', 0) !== false) {
                    $dot = dot($json_item);
                    // Trim before [
                    $pos = strpos($key, '[');
                    if ($pos !== false) {
                        $trimmedString = substr($key, 0, $pos);
                    }
                    $value = $dot->get($trimmedString);
                    if ($value !== NULL) {
                        // Filter the Numbers from String
                        $int_var = (int)filter_var($key, FILTER_SANITIZE_NUMBER_INT);
                        //Filter last variable from key
                        $lastDotPosition = strrpos($key, ".");
                        if ($lastDotPosition !== false) {
                            $last_variable = substr($key, $lastDotPosition + 1);
                            print_r(__LINE__, $last_variable);
                        }
                        $dot_array = dot($value[$int_var]);
                        $value_array = $dot_array->get($last_variable);
                        return $value_array;
                    }
                } else {
                    $dot = dot($json_item);
                    $value = $dot->get($key);
                    if ($value !== NULL) {
                        return $value;
                    }
                }
            } elseif (isset($json_item[$key])) {
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

    // Get the dataLayer json and json_decode it
    preg_match('~dataLayer\s*=\s*(.*?);</script>~' , $html, $match);
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
