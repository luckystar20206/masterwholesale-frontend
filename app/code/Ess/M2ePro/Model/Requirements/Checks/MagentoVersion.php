<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Requirements\Checks;

use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\Constraint\MultiConstraint;

/**
 * Class MagentoVersion
 * @package Ess\M2ePro\Model\Requirements\Checks
 */
class MagentoVersion extends AbstractCheck
{
    //########################################

    public function isMeet()
    {
        try {
            return \Composer\Semver\Semver::satisfies($this->getReal(), $this->getCompatibilityPattern());
        } catch (\Exception $e) {
            return false;
        }
    }

    //########################################

    public function getMin()
    {
        /** @var Constraint[] $constraints */
        $constraints = $this->collectConstraints(
            $this->getVersionParser()->parseConstraints($this->getCompatibilityPattern())
        );

        $minVersion = null;
        foreach ($constraints as $constraint) {
            $constraintVersion = $this->prepareVersion($constraint);
            if ($minVersion === null || $constraint->versionCompare($constraintVersion, $minVersion, '<')) {
                $minVersion = $constraintVersion;
            }
        }

        return $minVersion === null ? $this->getCompatibilityPattern() : $minVersion;
    }

    public function getReal()
    {
        return $this->getHelper('Magento')->getVersion();
    }

    //########################################

    public function getCompatibilityPattern()
    {
        return $this->getHelper('Magento')->isCommunityEdition()
            ? $this->getCommunityCompatibilityPattern()
            : $this->getEnterpriseCompatibilityPattern();
    }

    public function getCommunityCompatibilityPattern()
    {
        return $this->getReader()->getMagentoVersionData('community');
    }

    public function getEnterpriseCompatibilityPattern()
    {
        return $this->getReader()->getMagentoVersionData('enterprise');
    }

    //########################################

    private function collectConstraints(ConstraintInterface $constraint)
    {
        if ($constraint instanceof Constraint) {
            return [$constraint];
        }

        $constraints = [];

        if ($constraint instanceof MultiConstraint) {
            foreach ($constraint->getConstraints() as $constraintChild) {
                $constraints = array_merge($constraints, $this->collectConstraints($constraintChild));
            }
        }

        return $constraints;
    }

    private function prepareVersion(ConstraintInterface $constraint)
    {
        $replace = [
            // \Composer\Semver\VersionParser::$stabilities
            'stable', 'RC', 'beta', 'alpha', 'dev',
            // \Composer\Semver\Constraint\Constraint::$transOpStr
            '=', '==', '<', '<=', '>', '>=', '<>', '!=',
            '-', ' '
        ];

        return str_replace($replace, '', $constraint->__toString());
    }

    //########################################
}
