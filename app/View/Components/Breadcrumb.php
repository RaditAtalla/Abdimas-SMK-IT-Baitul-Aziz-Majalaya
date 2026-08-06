<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    
    public $item;
    public $active;
    public $link;
    public $subItem;
    public $subLink;
    public $sub2Item;
    public $sub2Link;
    public $sub3Item;
    public $sub3Link;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $item, $active, $link = null, 
        $subItem = null, $subLink = null,
        $sub2Item = null, $sub2Link = null,
        $sub3Item = null, $sub3Link = null
    ) {
        $this->item = $item;
        $this->active = $active;
        $this->link = $link;
        $this->subItem = $subItem;
        $this->subLink = $subLink;
        $this->sub2Item = $sub2Item;
        $this->sub2Link = $sub2Link;
        $this->sub3Item = $sub3Item;
        $this->sub3Link = $sub3Link;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
