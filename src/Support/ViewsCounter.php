<?php

namespace Nuclear\Hierarchy\Support;

use Nuclear\Hierarchy\SiteContent;
use CyrildeWit\EloquentViewable\Support\Period;
use CyrildeWit\EloquentViewable\View;
use Carbon\Carbon;

class ViewsCounter {

	/**
	 * Crunches statistics for a given model
	 *
	 * @param string|SiteContent $content
	 * @return array
	 */
	public function countFor($content = SiteContent::class)
	{
		$data = $this->compileStarterData($content);

		list($periods, $data) = $this->compilePeriods($data);

		return $this->countPeriods($content, $periods, $data); 
	}

	/**
	 * Compiles starter data
	 *
	 * @param string|SiteContent $content
	 * @return array
	 */
	protected function compileStarterData($content)
	{
		Carbon::setLocale(auth()->user()->locale);

		if(is_object($content)) {
			$latest = $content->views()->latest('viewed_at')->first();
			$totalViews = View::where('viewable_id', $content->id)->count();
			$viewsToday = View::where('viewable_id', $content->id)->whereBetween('viewed_at', [Carbon::today(), Carbon::tomorrow()])->count();
		} else {
			$latest = View::latest('viewed_at')->first();
			$totalViews = View::where('viewable_type', $content)->count();
			$viewsToday = View::where('viewable_type', $content)->whereBetween('viewed_at', [Carbon::today(), Carbon::tomorrow()])->count();
		}

		return [
			'total_views' =>$totalViews,
			'views_today' => $viewsToday,
			'latest_view' => $latest ? (new Carbon($latest->viewed_at))->diffForHumans() : __('foundation::general.never'),
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
	 * @param string|SiteContent $content
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
					$query = views($content)->collection($locale)->period(Period::create($periodSet[$i][0], $periodSet[$i][1]));

					if($i < ($c-1)) $query->remember();

					$data['data'][$key][$locale][] = $query->count();
				}
			}
		}

		return $data;
	}

}