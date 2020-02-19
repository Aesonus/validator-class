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

namespace Aesonus\Validator\Contracts;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
interface ValidatorInterface
{

    /**
     * Validates the input against the rules. Input should be provided as an
     * associative array or as an indexed array with each index being an associative
     * array of input values. If the name for a rule is not explicitly set, then the key
     * for each rule will serve as the name when outputting messages.
     * @param array $input
     * @return $this
     */
    public function validateAll(array $input): self;

        /**
     * Returns true if validation passed
     * @return bool|null Returns null if no validation has been performed
     */
    public function passed(): ?bool;

    /**
     * Returns true if validation failed
     * @return bool|null Returns null if no validation has been performed
     */
    public function failed(): ?bool;

    /**
     * Throws an exception containing all error messages in an easy to process manner
     * @throws \Aesonus\Validator\ValidatorClassException
     * @return void
     */
    public function asserted(): void;

    /**
     * Returns all the errors generated, if any. The output array will be indexed
     * for each set of inputs validated (even if only 1 was given).
     * @return array|null Returns null if no validation has been performed
     */
    public function errors(): ?array;
}
