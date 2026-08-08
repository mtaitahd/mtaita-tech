<?php
class HtmlCssValidator {

    public function validate($code, $ruleJson) {
        $rules = json_decode($ruleJson, true);
        if (!is_array($rules)) {
            return ['passed' => false, 'error' => 'Invalid test rule format.'];
        }
        $checks = isset($rules['checks']) ? $rules['checks'] : (isset($rules[0]) ? $rules : []);
        if (empty($checks)) {
            return ['passed' => false, 'error' => 'No checks defined in this test case.'];
        }
        $dom = $this->parseHtml($code);
        if (!$dom) {
            return ['passed' => false, 'error' => 'Invalid or empty HTML could not be parsed.'];
        }
        $xp = new DOMXPath($dom);
        $failed = [];
        foreach ($checks as $i => $check) {
            $res = $this->checkOne($dom, $xp, $check);
            if (!$res['ok']) {
                $failed[] = 'Check ' . ($i + 1) . ': ' . $res['message'];
            }
        }
        if (!empty($failed)) {
            return ['passed' => false, 'details' => implode("\n", $failed)];
        }
        return ['passed' => true, 'details' => 'All ' . count($checks) . ' checks passed.'];
    }

    private function checkOne($dom, $xp, $check) {
        if (!is_array($check)) {
            return ['ok' => false, 'message' => 'Malformed check.'];
        }
        $selector = $check['selector'] ?? ($check['tag'] ?? null);
        if (!$selector) {
            return ['ok' => false, 'message' => 'Missing selector/tag.'];
        }
        $nodes = $this->querySelectorAll($xp, $selector);
        if ($nodes->length === 0) {
            return ['ok' => false, 'message' => 'Required element "' . $selector . '" was not found.'];
        }
        $node = $nodes->item(0);

        if (isset($check['attrs']) && is_array($check['attrs'])) {
            foreach ($check['attrs'] as $attrSpec) {
                if (is_array($attrSpec)) {
                    $attrName = $attrSpec[0] ?? '';
                    $attrValue = $attrSpec[1] ?? null;
                } else {
                    $parts = explode('=', $attrSpec, 2);
                    $attrName = trim($parts[0]);
                    $attrValue = isset($parts[1]) ? trim($parts[1], "\"'") : null;
                }
                if ($node->hasAttribute($attrName)) {
                    if ($attrValue !== null) {
                        $actual = $node->getAttribute($attrName);
                        if (strcasecmp($actual, $attrValue) !== 0) {
                            return ['ok' => false, 'message' => 'Element "' . $selector . '" has "' . $attrName . '" = "' . $actual . '" but expected "' . $attrValue . '".'];
                        }
                    }
                } else {
                    return ['ok' => false, 'message' => 'Element "' . $selector . '" is missing the "' . $attrName . '" attribute.'];
                }
            }
        }

        if (isset($check['text']) && $check['text'] !== '' && $check['text'] !== null) {
            $text = $node->textContent;
            if (stripos($text, (string)$check['text']) === false) {
                return ['ok' => false, 'message' => 'Element "' . $selector . '" should contain text "' . $check['text'] . '".'];
            }
        }

        return ['ok' => true, 'message' => 'OK'];
    }

    private function querySelectorAll($xp, $selector) {
        $selector = trim($selector);
        $simple = '/^[a-zA-Z][a-zA-Z0-9-]*$/';
        if (preg_match($simple, $selector)) {
            return $xp->query('//' . strtolower($selector));
        }
        $attrPattern = '/^([a-zA-Z][a-zA-Z0-9-]*)\[([a-zA-Z][a-zA-Z0-9-]*)(?:=([\'"]?)([^\'"]*)\3)?\]$/';
        if (preg_match($attrPattern, $selector, $m)) {
            $tag = strtolower($m[1]);
            $attr = $m[2];
            $value = $m[4] ?? '';
            if (isset($m[3]) && $m[3] !== '') {
                return $xp->query('//' . $tag . '[@' . $attr . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"]');
            }
            return $xp->query('//' . $tag . '[@' . $attr . ']');
        }
        $classPattern = '/^\.([a-zA-Z][a-zA-Z0-9-_]*)$/';
        if (preg_match($classPattern, $selector, $m)) {
            return $xp->query('//*[contains(concat(" ", normalize-space(@class), " "), " ' . $m[1] . ' ")]');
        }
        $idPattern = '/^#([a-zA-Z][a-zA-Z0-9-_]*)$/';
        if (preg_match($idPattern, $selector, $m)) {
            return $xp->query('//*[@id="' . $m[1] . '"]');
        }
        if ($selector === '*') {
            return $xp->query('//*');
        }
        return $xp->query('//' . strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $selector)));
    }

    private function parseHtml($code) {
        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $code, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return $loaded ? $dom : null;
    }

    public function validateCss($css, $ruleJson) {
        $rules = json_decode($ruleJson, true);
        if (!is_array($rules)) {
            return ['passed' => false, 'error' => 'Invalid test rule format.'];
        }
        $checks = isset($rules['checks']) ? $rules['checks'] : (isset($rules[0]) ? $rules : []);
        if (empty($checks)) {
            return ['passed' => false, 'error' => 'No checks defined in this test case.'];
        }
        $blocks = $this->parseCssBlocks($css);
        $failed = [];
        foreach ($checks as $i => $check) {
            if (!is_array($check)) {
                $failed[] = 'Check ' . ($i + 1) . ': malformed check.';
                continue;
            }
            $selector = $check['css_selector'] ?? ($check['selector'] ?? null);
            if (!$selector) {
                $failed[] = 'Check ' . ($i + 1) . ': missing selector.';
                continue;
            }
            $found = false;
            $blockBody = '';
            foreach ($blocks as $block) {
                if ($this->selectorsMatch($block['selectors'], $selector)) {
                    $found = true;
                    $blockBody = $block['body'];
                    break;
                }
            }
            if (!$found) {
                $failed[] = 'Check ' . ($i + 1) . ': no CSS rule found for selector "' . $selector . '".';
                continue;
            }
            if (isset($check['css_property'])) {
                $prop = strtolower(trim($check['css_property']));
                if (!preg_match('/(?:^|;|})\s*' . preg_quote($prop, '/') . '\s*:/i', $blockBody)) {
                    $failed[] = 'Check ' . ($i + 1) . ': rule "' . $selector . '" should declare "' . $prop . '".';
                    continue;
                }
                if (isset($check['css_value']) && $check['css_value'] !== '') {
                    if (!preg_match('/(?:^|;|})\s*' . preg_quote($prop, '/') . '\s*:\s*[^;]*' . preg_quote($check['css_value'], '/') . '/i', $blockBody)) {
                        $failed[] = 'Check ' . ($i + 1) . ': rule "' . $selector . '" should set "' . $prop . '" to "' . $check['css_value'] . '".';
                    }
                }
            }
        }
        if (!empty($failed)) {
            return ['passed' => false, 'details' => implode("\n", $failed)];
        }
        return ['passed' => true, 'details' => 'All ' . count($checks) . ' checks passed.'];
    }

    private function parseCssBlocks($css) {
        $blocks = [];
        $len = strlen($css);
        $pos = 0;
        while ($pos < $len) {
            $open = strpos($css, '{', $pos);
            if ($open === false) break;
            $close = strpos($css, '}', $open);
            if ($close === false) break;
            $selectorText = substr($css, $pos, $open - $pos);
            $body = substr($css, $open + 1, $close - $open - 1);
            $selectors = array_filter(array_map('trim', explode(',', $selectorText)), function ($s) { return $s !== ''; });
            $blocks[] = ['selectors' => $selectors, 'body' => $body];
            $pos = $close + 1;
        }
        return $blocks;
    }

    private function selectorsMatch($blockSelectors, $selector) {
        $target = $this->normalizeSelector($selector);
        foreach ($blockSelectors as $s) {
            if ($this->normalizeSelector($s) === $target) return true;
            if (strpos($this->normalizeSelector($s), $target) !== false) return true;
        }
        return false;
    }

    private function normalizeSelector($s) {
        return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    }

    private function extractCss($code) {
        preg_match_all('#<style[^>]*>(.*?)</style>#is', $code, $m);
        return implode("\n", $m[1]);
    }
}
