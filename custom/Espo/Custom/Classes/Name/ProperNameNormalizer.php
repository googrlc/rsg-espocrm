<?php

namespace Espo\Custom\Classes\Name;

class ProperNameNormalizer
{
    /** @var array<string, string> */
    private const BUSINESS_TOKENS = [
        'llc' => 'LLC',
        'llp' => 'LLP',
        'lp' => 'LP',
        'inc' => 'Inc.',
        'corp' => 'Corp.',
        'ltd' => 'Ltd.',
        'pc' => 'P.C.',
        'pllc' => 'PLLC',
        'dba' => 'DBA',
        'usa' => 'USA',
    ];

    /**
     * @return null Keep original value (already mixed case or no letters).
     */
    public function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $collapsed = preg_replace('/\s+/u', ' ', trim($value));
        if ($collapsed === '') {
            return '';
        }

        if (!$this->shouldNormalize($collapsed)) {
            return null;
        }

        $words = preg_split('/\s+/u', $collapsed, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];

        foreach ($words as $word) {
            $out[] = $this->normalizeWord($word);
        }

        return implode(' ', $out);
    }

    private function shouldNormalize(string $value): bool
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $value);
        if ($letters === '') {
            return false;
        }

        $upper = mb_strtoupper($letters, 'UTF-8');
        $lower = mb_strtolower($letters, 'UTF-8');

        return $letters === $upper || $letters === $lower;
    }

    private function normalizeWord(string $word): string
    {
        if (str_contains($word, '-')) {
            $parts = explode('-', $word);

            return implode('-', array_map(fn ($p) => $this->normalizeSimpleToken($p), $parts));
        }

        return $this->normalizeSimpleToken($word);
    }

    private function normalizeSimpleToken(string $token): string
    {
        if ($token === '') {
            return '';
        }

        $lower = mb_strtolower($token, 'UTF-8');
        $t = mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');

        $t = preg_replace_callback(
            "/(?<=[\x{0027}\x{2019}])([[:alpha:]])/u",
            static fn (array $m): string => mb_strtoupper($m[1], 'UTF-8'),
            $t
        );

        if (preg_match('/^Mc([[:alpha:]][\p{L}\x{0027}\x{2019}]*)$/u', $t, $m)) {
            $rest = $m[1];
            $first = mb_strtoupper(mb_substr($rest, 0, 1, 'UTF-8'), 'UTF-8');
            $tail = mb_substr($rest, 1, null, 'UTF-8');
            $t = 'Mc' . $first . $tail;
        }

        $plain = mb_strtolower(preg_replace('/\.+$/u', '', $t), 'UTF-8');

        return self::BUSINESS_TOKENS[$plain] ?? $t;
    }
}
