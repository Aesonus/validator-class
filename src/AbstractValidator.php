<?php
/*
 *
 * This file is a member of the aesonus/validator-class package
 *
 * Copyright (c) 2020 Cory Laughlin <corylcomposinger at gmail.com>
 *
 * For full copyright and license information, please refer to the LICENSE
 * file that was distributed with this software
 */

namespace Aesonus\Validator;

use Aesonus\Validator\Contracts\ValidatorInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

/**
 * Validates the input against the rules that are returned by getRules().
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     *
     * @var array|null
     */
    protected $errors;

    /**
     *
     * @var ?array
     */
    protected $passedFields;

    private $hasValidated = false;

    public function errors(): ?array
    {
        return $this->errors;
    }

    public function failed(): ?bool
    {
        if (!$this->hasValidated) {
            return null;
        }
        return !empty($this->errors);
    }

    public function passed(): ?bool
    {
        if (!$this->hasValidated) {
            return null;
        }
        return !$this->failed();
    }

    public function validateAll(array $input): ValidatorInterface
    {
        $this->hasValidated = true;
        if (empty(array_filter(array_keys($input), 'is_int'))) {
            $input = [$input];
        }

        foreach ($input as $index => $inputRow) {
            array_map(function ($field) use ($index, $inputRow) {
                $input = isset($inputRow[$field]) ? $inputRow[$field] : null;
                $this->assert($index, $field, $input);
            }, array_keys($this->getRules()));
        }
        return $this;
    }

    public function asserted(): void
    {
        if ($this->failed()) {
            throw new ValidatorClassException(
                json_encode([
                    'errors' => $this->errors(),
                    'passed' => $this->passedFields,
                    ], JSON_PRETTY_PRINT
            ));
        }
    }

    private function assert($index, $field, $input)
    {
        try {
            $rules = $this->getRules();
            if ($rules[$field]->getName() === null) {
                $rules[$field]->setName(ucwords(str_replace('_', ' ', $field)));
            }
            $rules[$field]->assert($input);
            $this->passedFields[$index][] = $field;
        } catch (NestedValidationException $exc) {
            $this->errors[$index][$field] = $exc->getMessages();
        }
    }

    /**
     * Must return the rules for the validation in a keyed array. Each key
     * must correspond to a field.
     * @return Validatable[]
     */
    abstract public function getRules(): array;
}
