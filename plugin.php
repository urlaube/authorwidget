<?php

  /**
    This is the AuthorWidget plugin.

    This file contains the AuthorWidget plugin. It provides a widget that lists
    all available authors.

    @package urlaube\authorwidget
    @version 0.1a2
    @author  Yahe <hello@yahe.sh>
    @since   0.1a0
  */

  // ===== DO NOT EDIT HERE =====

  // prevent script from getting called directly
  if (!defined("URLAUBE")) { die(""); }

  class AuthorWidget extends BaseSingleton implements Plugin {

    // RUNTIME FUNCTIONS

    public static function plugin() {
      $result = null;

      $authors = [];
      if (!getcache(null, $authors, static::class)) {
        callcontent(null, true, true,
                    function ($content) use (&$authors) {
                      $result = null;

                      // check that $content is not hidden
                      if (!istrue(value($content, HidePlugin::HIDDEN))) {
                        // check that $content is not hidden from author
                        if (!istrue(value($content, HidePlugin::HIDDENFROMAUTHOR))) {
                          // check that $content is not a relocation
                          if (null === value($content, RelocatePlugin::RELOCATE)) {
                            // read the author
                            $authorvalue = value($content, AUTHOR);
                            if (null !== $authorvalue) {
                              // make sure that only valid characters are contained
                              if (1 === preg_match("~^[0-9A-Za-z\_\-]+$~", $authorvalue)) {
                                $authorvalue = strtolower($authorvalue);

                                if (isset($authors[$authorvalue])) {
                                  $authors[$authorvalue]++;
                                } else {
                                  $authors[$authorvalue] = 1;
                                }
                              }
                            }
                          }
                        }
                      }

                      return null;
                    });

        setcache(null, $authors, static::class);
      }

      if (0 < count($authors)) {
        // sort the authors
        ksort($authors);

        $content = fhtml("<div>".NL);
        foreach ($authors as $key => $value) {
          $metadata = new Content();
          $metadata->set(AUTHOR, $key);

          $content .= fhtml("  <span class=\"glyphicon glyphicon-user\"></span> <a href=\"%s\">%s</a> (%d)".BR.NL,
                            AuthorHandler::getUri($metadata),
                            $key,
                            $value);
        }
        $content .= fhtml("</div>");

        $result = new Content();
        $result->set(CONTENT, $content);
        $result->set(TITLE,   t("Autoren", static::class));
      }

      return $result;
    }

  }

  // register plugin
  Plugins::register(AuthorWidget::class, "plugin", ON_WIDGETS);

  // register translation
  Translate::register(__DIR__.DS."lang".DS, AuthorWidget::class);
