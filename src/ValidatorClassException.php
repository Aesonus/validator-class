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

/**
 * Description of ValidatorClassException
 *
 * @author Cory Laughlin <corylcomposinger at gmail.com>
 */
class ValidatorClassException extends \RuntimeException
{
    /**
     *
     * @var array
     */
    protected $messages;

    /**
     *
     * @var array
     */
    protected $passedFields;

    public function __construct(string $message = "", int $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $msg = json_decode($message, true);
        $this->messages = $msg['errors'];
        $this->passedFields = $msg['passed'];
    }

    public function getPassedFields(?int $row_index = null): array
    {
        if (isset($row_index)) {
            return $this->passedFields[$row_index] ?? [];
        } else {
            return $this->getAllPassedFields();
        }
    }

    private function getAllPassedFields(): array
    {
        return $this->passedFields ?? [];
    }

    /**
     * Gets the messages for all form rows or a specific form row
     * @param int|null $row_index
     * @return array
     */
    public function getMessages(?int $row_index = null): array
    {
        if (isset($row_index)) {
            return $this->getRowMessages($row_index);
        } else {
            return $this->getAllMessages();
        }
    }

    private function getAllMessages(): array
    {
        return $this->messages ?? [];
    }

    private function getRowMessages(int $row): array
    {
        return $this->messages[$row];
    }
}
