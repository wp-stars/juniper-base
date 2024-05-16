import React, {useState, useMemo, useContext, useEffect} from 'react';
import {DataContext} from '../DataProvider';
import styles from './range.module.css';
import Chart from './chart';
import {
	formatDateYYYYMMDD,
	formatPercentage,
	getAverage,
	getBaseColor,
	getDataInRange,
	getTrend,
	numberToEuro,
} from '../functions';
import dayjs from 'dayjs'
const {ranges} = wpVars;

const Range = () => {
	const {data, setData} = useContext(DataContext);

	const onRangeSelect = (evt) => {
		evt.preventDefault();
		if (!data.isLoading) {
			setData({
				...data,
				selectedRange: parseInt(evt.target.dataset.days),
				after: dayjs().subtract(parseInt(evt.target.dataset.days), 'days'),
				before: dayjs(),
			});
		}
	};

	return (
		<div className={styles.container}>
			{ranges && ranges.length && ranges.map((elem) => {
				const dataInRange = getDataInRange(data.previewData, 'post_date', dayjs().subtract(parseInt(elem.days), 'days'), dayjs());
				if (!dataInRange.length) {
					return;
				}
				return (
					<div className={[styles.range, (data.selectedRange === elem.days) && 'active'].join(' ')} key={elem.key} data-key={elem.key} data-days={elem.days} onClick={onRangeSelect}>
						<div className={styles.infoWrapper}>
							<div className={styles.infoPrice}>
								<span className={styles.averagePrice}>{numberToEuro((dataInRange.length <= 2) ? dataInRange[dataInRange.length - 1].price : getAverage(dataInRange), data.selectedMetalUnit)}</span>
								<span className={styles.priceChange} style={{color: getBaseColor(getTrend(dataInRange))}}>{formatPercentage(getTrend(dataInRange) / dataInRange[dataInRange.length - 1].price * 100)}</span>
							</div>
							<span className={styles.label}>{elem.label}</span>
						</div>
						<div className={styles.chartWrapper}>
							<Chart data={dataInRange} small={true} range={elem.days} />
						</div>
					</div>
				);
			})}
		</div>
	);
};

export default Range;
