<?php
/*
 * This file is part of the CommonBundle package.
 *
 * (c) Stermedia <http://stermedia.pl/>
 */

namespace Stermedia\Bundle\CommonBundle\Extension\Twig;

/**
 * Localized Date Extension
 *
 * @package    CommonBundle
 * @subpackage TwigExtensions
 * @author     Jakub Paszkiewicz <paszkiewicz.jakub@gmail.com>
 */
class LocalizedDateExtension extends \Twig_Extension
{
    /**
     * Constructor
     */
    public function __construct()
    {
        if (!class_exists('IntlDateFormatter')) {
            throw new \RuntimeException('The intl extension is needed to use intl-based filters.');
        }
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'localizedDate' => new \Twig_Filter_Method($this, 'twigLocalizedDateFilter'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'localizedDate';
    }

    /**
     * Localized Date Filter
     *
     * @param mixed  $date       date as string or DateTime
     * @param string $dateFormat [default='medium'] date format
     * @param string $timeFormat [default='medium'] dtime format
     * @param string $locale     [default=null] locale
     *
     * @return string
     */
    public function twigLocalizedDateFilter($date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null)
    {
        $formatValues = array(
            'none'   => \IntlDateFormatter::NONE,
            'short'  => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long'   => \IntlDateFormatter::LONG,
            'full'   => \IntlDateFormatter::FULL,
        );

        $formatter = \IntlDateFormatter::create(
            $locale !== null ? $locale : \Locale::getDefault(),
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            date_default_timezone_get()
        );

        if (!$date instanceof \DateTime) {
            if (ctype_digit((string) $date)) {
                $date = new \DateTime('@'.$date);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } else {
                $date = new \DateTime($date);
            }
        }

        return $formatter->format($date->getTimestamp());
    }
}
