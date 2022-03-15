<?php

declare(strict_types=1);

namespace APIcation\TracyModules;

use Tracy\IBarPanel;

class CApplicationTracy implements IBarPanel
{
    /**
     * @var array
     */
    private array $barData;

    /**
     * @param array $barData Data for panel
     */
    public function __construct(
      array $barData
    ){
        $this->barData = $barData;
    }

    public function getTab()
    {
        return '<span title="Vysvětlující popisek"><span class="tracy-label">CApp</span></span>';
    }

    public function getPanel()
    {
        $res = '<table class="capp-panel">';
        foreach ($this->barData as $name => $val) {
            $res .= '<tr class=""><td class="propname">' . $name . '</td>';
            // either append or export data if not string
            $res .= '<td class="data">' . (is_string($val) ? $val : var_export($val, true)) . '</td></tr>';
        }

        return $res . '</table>';
    }
}
