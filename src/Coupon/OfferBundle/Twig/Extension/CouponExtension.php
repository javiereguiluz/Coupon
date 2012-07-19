<?php

namespace Coupon\OfferBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Custom Twig extension with some filters and functions used in the
 * application templates.
 */
class CouponExtension extends \Twig_Extension
{
    private $translator;

    public function __construct(TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function getFilters()
    {
        return array(
            'show_as_list' => new \Twig_Filter_Method($this, 'showAsList', array('is_safe' => array('html'))),
            'countdown'    => new \Twig_Filter_Method($this, 'countdown', array('is_safe' => array('html'))),
            'i18n_date'    => new \Twig_Filter_Method($this, 'i18nDate'),
        );
    }

    public function getFunctions()
    {
        return array(
            'discount' => new \Twig_Function_Method($this, 'discount')
        );
    }

    /**
     * Renders a text content as an HTML list, transformin each "\n" into
     * a new list item.
     *
     * @param string $value The text to tbe transformed
     * @param string $type  The type of the list ('ul', 'ol')
     */
    public function showAsList($value, $type='ul')
    {
        $html = "<".$type.">\n";
        $html .= "  <li>".str_replace("\n", "</li>\n  <li>", $value)."</li>\n";
        $html .= "</".$type.">\n";

        return $html;
    }

    /**
     * Renders a dynamic JavaScript countdown to the given date. It allows to
     * include several countdowns in the same page.
     *
     * @param string $date The object that represents the date.
     */
    public function countdown($date)
    {
        $date = json_encode(array(
            'year'   => $date->format('Y'),
            'month'  => $date->format('m')-1, // JavaScript months start at 0, but PHP starts at 1
            'day'    => $date->format('d'),
            'hour'   => $date->format('H'),
            'minute' => $date->format('i'),
            'second' => $date->format('s')
        ));

        $random_id = 'countdown-'.rand(1, 100000);
        $html = <<<EOJ
        <span id="$random_id"></span>

        <script type="text/javascript">
        expires_function = function(){
            var expires = $date;
            showCountdown('$random_id', expires);
        }
        if (!window.addEventListener) {
            window.attachEvent("onload", expires_function);
        } else {
            window.addEventListener('load', expires_function);
        }
        </script>
EOJ;

        return $html;
    }

    /**
     * Renders the given date with the format and locale passed as arguments.
     *
     * @param string $date       The object tha represents the date
     * @param string $dateFormat The format applied to the date part of the date object
     * @param string $timeFormat The format applied to the time part of the date object
     * @param string $locale     The locale used to display the date
     */
    public function i18nDate($date, $dateFormat = 'medium', $timeFormat = 'none', $locale = null)
    {
        // Copied from:
        //   https://github.com/thaberkern/symfony/blob
        //   /b679a23c331471961d9b00eb4d44f196351067c8
        //   /src/Symfony/Bridge/Twig/Extension/TranslationExtension.php

        // Available formats: http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
        $formats = array(
            'none'   => \IntlDateFormatter::NONE,   // (it displays nothing)
            'short'  => \IntlDateFormatter::SHORT,  // 12/13/52  3:30pm
            'medium' => \IntlDateFormatter::MEDIUM, // Jan 12, 1952
            'long'   => \IntlDateFormatter::LONG,   // January 12, 1952  3:30:32pm
            'full'   => \IntlDateFormatter::FULL,   // Tuesday, April 12, 1952 AD  3:30:42pm PST
        );

        $formatter = \IntlDateFormatter::create(
            $locale != null ? $locale : $this->getTranslator()->getLocale(),
            $formats[$dateFormat],
            $formats[$timeFormat]
        );

        if ($date instanceof \DateTime) {
            return $formatter->format($date);
        } else {
            return $formatter->format(new \DateTime($date));
        }
    }

    /**
     * Returns the given discount as a percentage.
     *
     * @param string $price    The final retail price
     * @param string $discount The discount to the original price
     * @param string $decimals The number of decimals to display
     */
    public function discount($price, $discount, $decimals = 0)
    {
        if (!is_numeric($price) || !is_numeric($discount)) {
            return '-';
        }

        if ($discount == 0 || $discount == null) {
            return '0%';
        }

        $original_price = $price + $discount;
        $percent = ($discount / $original_price) * 100;

        return '-'.number_format($percent, $decimals).'%';
    }

    public function getName()
    {
        return 'Coupon';
    }
}
