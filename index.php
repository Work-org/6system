<?php

namespace sixSystem\test;

interface Actions
{
    /** @return string */
    public function toString();

    /**
     * Get param value
     * @param $name
     * @return mixed
     */
    public function getParams($name);
}

interface Driven
{
    /** @param $speed float */
    public function setSpeed($speed);

    /** @param $accelerate float */
    public function setAccelerate($accelerate);

    /**
     * @param int $level
     * @return bool
     */
    public function callLift($level);
}

interface Lifting
{
    /** @param int */
    public function setLiftingCapacity($capacity);
}

interface Geometry
{
    /** @param array $aDimension */
    public function setDimensions(array $aDimension);

    /**
     * @param array $dimension
     * @return int
     */
    public function getVolume(array $dimension);
}

/** Params class */
abstract class Params
{

    /**
     * Params constructor.
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        foreach ($aParams as $aParam) {
            $this->setParam($aParam);
        }
    }

    /**
     * Getter property param
     * @param string $key
     * @return mixed
     */
    public function getParam($key)
    {
        return property_exists($this, $key) ? $this->{$name} : null;
    }

    /**
     * Setter property
     * @param array $aParam
     */
    private function setParam(array $aParam)
    {
        reset($aParam);
        $property = key($aParam);

        if (property_exists($this, $property)) {
            $this->{$property} = $aParam[$property];
        }
    }
}

/** Additional param lift */
class ParamsLift extends Params
{
    /** @var  bool */
    public $mirror;
    /** @var bool */
    public $handrail;
    /** @var  bool */
    public $forInvalids;
}

/** Класс обекта строительства */
abstract class ObjectBuilding implements Actions
{
    /* Высота этажа */
    const WIGHT_LEVEL = 3;

    /**
     * @param string $name
     * @return mixed
     */
    public function getParams($name)
    {
        return $this->{$name};
    }

    /**
     * Get line public object var
     * @return string
     */
    public function toString()
    {
        return json_encode(get_object_vars($this));
    }

    /**
     * Return volume cube object
     * @param array $dimension
     * @return int
     */
    protected function getVolume(array $dimension)
    {
        return (count($dimension) > 1) ? array_shift($dimension) * $this->getVolume($dimension) : null;
    }
}

/** Класс лифта, наследуемого от объекта строительства,
 * возможно нужно иметь еще одного предка,
 * например клас Здания, с дополнительными параметрами и функциями */
class Lift extends ObjectBuilding implements Driven, Lifting, Geometry
{

    /* Direction move lift */
    const DIRECTION_UP = 0;
    const DIRECTION_DOWN = 1;

    const MAX_LEVEL_UP = 5;
    const MIN_LEVEL_DOWN = 0;

    const FIXED_SPEED = 2.5;

    /** @var array Геометрия лифта, Ш,В,Д */
    public $dimensions;
    /** @var float Скорость лифта постоянная
     * на максимальном ускорении */
    public $speed;
    /** @var float Ускорения */
    public $accelerate;
    /** @var int Грузоподъемность */
    public $capacity;
    /** @var ParamsLift */
    public $oParams;
    /** @var  int */
    public $level;

    public function __construct()
    {
        // init start value
        $this->level = self::MAX_LEVEL_UP;
    }

    /** @param float $accelerate */
    public function setAccelerate($accelerate)
    {
        $this->accelerate = $accelerate;
    }

    /**  @param array $aDimension */
    public function setDimensions(array $aDimension)
    {
        $this->dimensions = $aDimension;
    }

    public function setLiftingCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    /** @param float $speed */
    public function setSpeed($speed)
    {
        $this->speed = $speed;
    }

    /**
     * Get volume lift
     * @param array $dimension
     * @return int
     */
    public function getVolume(array $dimension)
    {
        return parent::getVolume($this->dimensions);
    }

    /**
     * Process call lift
     * @param int $level
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function callLift($level)
    {
        if ($level > self::MAX_LEVEL_UP
            || $level < self::MIN_LEVEL_DOWN
        ) {
            throw new \InvalidArgumentException('Not correctly selected level');
        }

        if ($level === $this->level) {

            return true;
        }

        try {
            while ($level !== $this->level) {
                $this->updateLevel($level);
            }
        } catch (CustomException $exception) {
            // @todo create action out
            return false;
        }

        return true;
    }

    /**
     * Update lift level
     * @param int $level
     * @throws \sixSystem\test\CustomException
     */
    private function updateLevel($level)
    {
        if ($level > $this->level
            && $level !== $this->level
        ) {
            $this->run(self::DIRECTION_DOWN);
            $this->level--;
        } else {
            $this->run(self::DIRECTION_UP);
            $this->level++;
        }
    }

    /**
     * Process moved lift between floor
     * @param int $direction
     * @throws CustomException
     */
    private function run($direction)
    {
        switch ($direction) {
            case self::DIRECTION_DOWN:
                $timeWhilePasFloorDown = 5;
                /*@todo loop (математика подсчет, зависимость по скорости и ускорению, времени для прохода этажа)*/
                // use this[accelerate] and this[speed]
                usleep(1000 * $timeWhilePasFloorDown);
                break;

            case self::DIRECTION_UP:
                $timeWhilePasFloorUp = 8;
                /*@todo loop (математика подсчет, зависимость по скорости и ускорению, времени для прохода этажа)*/
                usleep(1000 * $timeWhilePasFloorUp);
                break;

            default:
                throw new CustomException('Some error happened');
        }
    }
}

class CustomException extends \Exception
{
}

$oLift = new Lift();
$oLift->oParams = new ParamsLift(
    [
        'mirror' => true,
        'handrail' => true,
        'forInvalids' => false,
    ]
);

echo $oLift->toString().PHP_EOL;

// Вызвали лифт на этаж 1
// лифт стоит на 5-м
if ($oLift->callLift(1)) {
    echo 'Lift filed'.PHP_EOL;
}