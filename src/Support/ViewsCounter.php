<?php

namespace Nuclear\Hierarchy\Support;

use Nuclear\Hierarchy\Content;
use CyrildeWit\EloquentViewable\Support\Period;
use CyrildeWit\EloquentViewable\View;
use Carbon\Carbon;

class ViewsCounter {

	/**
	 * Crunches statistics for a given model
	 *
	 * @param string|Content $content
	 * @return array
	 */
	public function countFor($content = Content::class)
	{
		$data = $this->compileStarterData($content);

		list($periods, $data) = $this->compilePeriods($data);

		return $this->countPeriods($content, $periods, $data); 
	}

	/**
	 * Compiles starter data
	 *
	 * @param string|Content $content
	 * @return array
	 */
	protected function compileStarterData($content)
	{
		$latest = is_object($content)
			? $content->views()->latest('viewed_at')->first()
			: View::latest('viewed_at')->first();

		return [
			'total_views' => views($content)->unique()->count(),
			'views_today' => views($content)->unique()->period(Period::create(Carbon::today(), Carbon::tomorrow()))->count(),
			'latest_view' => $latest ? $latest->viewed_at->diffForHumans() : __('foundation::general.never'),
			'labels' => [],
			'data' => []
		];
	}

	/**
	 * Compiles query periods
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function compilePeriods(array $data)
	{
		$periods = $data['labels'] = ['last7' => [], 'last28' => []];

		$today = Carbon::today()->startOfDay();

		$last7Start = $today->copy()->subDays(6);
		$last7End = $last7Start->copy()->endOfDay();

		while($last7Start->lte($today)) {
			$periods['last7'][] = [$last7Start->copy(), $last7End->copy()];

			$data['labels']['last7'][] = $last7End->formatLocalized('%d %b');

			$last7Start->addDay();
			$last7End->addDay();
		}

		$last28Start = $today->copy()->subWeeks(4);
		$last28End = $last28Start->copy()->addWeek()->endOfDay();

		while($last28Start->lt($today)) {
			$periods['last28'][] = [$last28Start->copy(), $last28End->copy()];

			$data['labels']['last28'][] = $last28End->formatLocalized('%d %b');

			$last28Start->addWeek();
			$last28End->addWeek();
		}

		return [$periods, $data];
	}

	/**
	 * Returns the views counts for given periods
	 *
	 * @param string|Content $content
	 * @param array $periods
	 * @param array $data
	 * @return array
	 */
	protected function countPeriods($content, array $periods, array $data)
	{
		foreach($periods as $key => $periodSet) {
			foreach(config('app.locales') as $locale) {
				$c = count($periodSet);
				for($i = 0; $i < $c; $i++) {
					$query = views($content)->unique()->collection($locale)->period(Period::create($periodSet[$i][0], $periodSet[$i][1]));

					if($i < ($c-1)) $query->remember();

					$data['data'][$key][$locale][] = $query->count();
				}
			}
		}

		return $data;
	}

}