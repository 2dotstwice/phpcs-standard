<?php
/**
 * @file
 */

class TwoDotsTwice_Sniffs_Functions_ArgumentTypesSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var string[]
     */
    protected $excludedMethods;

    public function __construct()
    {
        $this->excludedMethods = [
            '__call',
            '__callStatic',
            '__get',
            '__isset',
            '__set',
            '__set_state',
            '__unset',
        ];
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        return array(T_FUNCTION);
    }

    /**
     * @inheritdoc
     */
    public function process(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr
    ) {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);

        // Exclude certain magic methods.
        if ($methodName && $this->isExcludedMethod($methodName)) {
            return;
        }

        $methodProperties = $phpcsFile->getMethodProperties($stackPtr);

        // Ignore private methods.
        if ($methodProperties['scope_specified'] && 'private' === $methodProperties['scope']) {
            return;
        }

        $arguments = $phpcsFile->getMethodParameters($stackPtr);
        foreach ($arguments as $argument) {
            $this->validateArgument($phpcsFile, $stackPtr, $argument);
        }
    }

    /**
     * @param string $methodName
     * @return bool
     */
    private function isExcludedMethod($methodName) {
        return in_array($methodName, $this->excludedMethods);
    }

    private function validateArgument(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $argument
    ) {
        if ($argument['type_hint'] == '') {
            $this->addMissingArgumentTypeError(
                $phpcsFile,
                $stackPtr,
                $argument
            );
        }
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @param string $argument
     * @throws PHP_CodeSniffer_Exception
     */
    private function addMissingArgumentTypeError(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $argument
    ) {
        $phpcsFile->getDeclarationName($stackPtr);

        $methodName = $phpcsFile->getDeclarationName($stackPtr);

        if ($methodName) {
            $err = "No type specified for argument {$argument['name']} of method {$methodName}";
        } else {
            $err = "No type specified for argument {$argument['name']} of closure";
        }

        $phpcsFile->addError($err, $stackPtr);
    }

}
