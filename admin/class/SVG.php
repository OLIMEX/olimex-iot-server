<?php 

class SVG {

	protected $data    = array();
	protected $type    = NULL;
	protected $size    = array();
	protected $min     = array();
	protected $max     = array();
	protected $span    = array();
	protected $step    = array();
	protected $measure = '';
	
	public function __construct(
		array &$data = array(), 
		$type = NULL, 
		$color = NULL,
		$width = 420,
		$height = 200,
		$measure = '',
		$init = NULL
	) {
		$this->data = &$data;
		$this->type = $type;
		
		$this->color = empty($color) ?
			'grey'
			:
			$color
		;
		
		$this->size = array(
			'x' => $width,
			'y' => $height
		);
		
		$this->measure = $measure;
		
		$this->init($init);
		$this->roundLimits();
	}
	
	protected function init($init) {
		foreach ($this->data as $i => &$data) {
			if (is_callable($init)) {
				call_user_func_array($init, array($this, $i, &$data));
			}
			
			if (!isset($prev)) {
				$prev = &$data;
			}
			
			$data['y'] = -$data['y'];
			if (empty($this->min) || empty($this->max)) {
				$this->min = array(
					'x'  => $data['x'], 
					'y'  => $data['y'],
					'dx' => 0,
					'dy' => 0
				);
				$this->max = array(
					'x' => $data['x'], 
					'y' => $data['y']
				);
			} else {
				$this->min['x'] = min($this->min['x'], $data['x']);
				$this->min['y'] = min($this->min['y'], $data['y']);
				
				$dx = abs($prev['x'] - $data['x']);
				$dy = abs($prev['y'] - $data['y']);
				
				$this->min['dx'] = $this->min['dx'] == 0 ?
					$dx
					:
					($dx > 0 ?
						min($this->min['dx'], $dx)
						:
						$this->min['dx']
					)
				;
				$this->min['dy'] = $this->min['dy'] == 0 ?
					$dy
					:
					($dy > 0 ?
						min($this->min['dy'], $dy)
						:
						$this->min['dy']
					)
				;
				
				$this->max['x'] = max($this->max['x'], $data['x']);
				$this->max['y'] = max($this->max['y'], $data['y']);
			}
			
			$prev = &$data;
		}
		
		if ($this->type == 'binary' && !empty($this->min) && !empty($this->max)) {
			$this->min['y'] = -1;
			$this->max['y'] = 0;
		}
		
		if (empty($this->min) || empty($this->max)) {
			$this->min = array(
				'x' => 0,
				'y' => 0
			);
			
			$this->max = array(
				'x' => $this->size['x'],
				'y' => $this->size['y']
			);
			
			$this->span = array(
				'x' => $this->size['x'],
				'y' => $this->size['y']
			);
		} else {
			$this->span = array(
				'x' => $this->min['x'] == $this->max['x'] ? 
					1
					: 
					$this->max['x'] - $this->min['x']
				,
				'y' => $this->min['y'] == $this->max['y'] ? 
					1
					: 
					$this->max['y'] - $this->min['y']
			);
		}
	}
	
	protected function roundLimits() {
		$this->step['y'] = $this->round(
			$this->span['y'] / 5, 
			TRUE
		);
		
		$this->min['y']  = floor($this->min['y'] / $this->step['y']) * $this->step['y'] - $this->step['y'] / 2;
		$this->max['y']  = max(
			ceil($this->max['y'] / $this->step['y']) * $this->step['y'],
			$this->min['y'] + $this->step['y']
		);
		$this->span['y'] = $this->max['y'] - $this->min['y'];
		// echo '<pre>'.$this->min['y'].' '.$this->max['y'].' '.$this->span['y'].'</pre>';
	}
	
	protected function round($number, $gt = TRUE) {
		static $desiredFractions = array(1, 2, 5, 10);
		
		$sign = $number < 0 ? -1 : 1;
		
		$exponent = floor(log10($sign * $number));
		$fraction = $number / pow(10, $exponent);
		
		foreach ($desiredFractions as $f) {
			if ($gt) {
				if ($fraction < $sign * $f) {
					$fraction = $sign * $f;
					break;
				}
			} else {
				if ($fraction > $sign * $f) {
					$fraction = $sign * $f;
					break;
				}
			}
		}
		
		return $fraction * pow(10, $exponent);
	}
	
	public function min() {
		return $this->min;
	}
	
	public function max() {
		return $this->max;
	}
	
	public function span() {
		return $this->span;
	}
	
	public function step() {
		return $this->step;
	}
	
	public function viewBox() {
		return 
			$this->min['x'].' '.$this->min['y'].' '.
			$this->span['x'].' '.$this->span['y']
		;
	}
	
	public function width() {
		return $this->size['x'];
	}
	
	public function height() {
		return $this->size['y'];
	}
	
	public function gradient() {
		$gradient = array();
		
		foreach ($this->data as &$data) {
			if (!isset($prev)) {
				$prev = &$data;
				$current = array(
					'offset'  => 0,
					'color'   => isset($data['color'])   ? $data['color'] : $this->color,
					'opacity' => isset($data['opacity']) ? $data['opacity'] : 1
				);
				$gradient[] = $current;
			}
			
			if (isset($data['color']) && $data['color'] != $current['color']) {
				$pOffset = round(($prev['x'] - $this->min['x']) / $this->span['x'] * 100, 3);
				$dOffset = round(($data['x'] - $this->min['x']) / $this->span['x'] * 100, 3);
				
				if ($pOffset != $current['offset']) {
					$current['offset'] = $pOffset;
					$gradient[] = $current;
				}
				
				$current = array(
					'offset'  => $dOffset,
					'color'   => $data['color'],
					'opacity' => isset($data['opacity']) ? $data['opacity'] : 1
				);
				$gradient[] = $current;
			}
			
			$prev = &$data;
		}
		
		if (empty($gradient)) {
			return $gradient;
		}
		
		if ($current['offset'] < 100) {
			$current['offset'] = 100;
			$gradient[] = $current;
		}
		
		return $gradient;
	}
	
	public function color() {
		return $this->color;
	}
	
	public function points() {
		$points = '';
		foreach ($this->data as &$data) {
			if (!isset($prev)) {
				$prev = &$data;
			}
			
			$dx = abs($prev['x'] - $data['x']);
			$dy = abs($prev['y'] - $data['y']);
			
			$points .= 
				($this->type == 'binary' && $prev['y'] != $data['y'] ?
					$data['x'].','.$prev['y'].' ' 
					: 
					''
				).
				($this->type != 'binary' && $dx > $this->min['dx'] * 3 && $dy > $this->min['dy'] * 10 ?
					($data['x'] - $this->min['dx']).','.$prev['y'].' ' 
					: 
					''
				).
				$data['x'].','.$data['y'].' '
			;
			
			$prev = &$data;
		}
		return $points;
	}
	
	public function gridSpan() {
		return array(
			'x' => $this->span['y'] * $this->size['x'] / $this->size['y'],
			'y' => $this->span['y']
		);
	}
	
	public function gridViewBox() {
		return 
			'0 '.$this->min['y'].' '.
			$this->gridSpan()['x'].' '.$this->gridSpan()['y']
		;
	}
	
	public function yGridPath() {
		$path = '';
		
		$y = $this->max['y'];
		$maxX = $this->gridSpan()['x'];
		while ($y >= $this->min['y']) {
			$path .= 'M0 '.$y.' L'.$maxX.' '.$y.' ';
			$y -= $this->step['y'];
		}
		
		return $path;
	}
	
	public function yGridText($fontSize = 20) {
		$text = array();
		
		$fontOffset = round($this->step['y'] / 10, 3);
		$fontSize   = round($this->gridSpan()['y'] * $fontSize / $this->size['y'], 3);
		
		$y = $this->max['y'];
		while ($y >= $this->min['y']) {
			$text[] = array(
				'x' => $this->min['x'],
				'y' => $y - $fontOffset,
				'font-size' => $fontSize,
				'fill' => 'black',
				'text' => (-$y).($this->measure)
			);
			$y -= $this->step['y'];
		}
		
		return $text;
	}
	
}
