import {useContext, useEffect} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {DataContext} from '../DataProvider';
import {DatePicker} from '@mui/x-date-pickers/DatePicker';
import TextField from '@mui/material/TextField';
import {LocalizationProvider} from '@mui/x-date-pickers/LocalizationProvider';
import {AdapterDateFns} from '@mui/x-date-pickers/AdapterDateFns';
import {enGB, de} from 'date-fns/locale';
import dayjs from 'dayjs';
import {
	formatDateYYYYMMDD,
	formatPercentage,
	getAverage,
	getBaseColor,
	getDataInRange,
	getTrend,
	numberToEuro,
} from '../functions';
import Chart from './chart';
import styles from './chartWrapper.module.css';
import {useState} from '@wordpress/element';
import axios from 'axios'
const {restUrl} = wpVars;

const ChartWrapper = () => {
	const {data, setData} = useContext(DataContext);
	const [dataInRange, setDataInRange] = useState(getDataInRange(data.prices, 'post_date', data.after, data.before));
	let error = '';

	const getData = async (getPreviewData = false) => {
		axios.get(`${restUrl}ls/v1/metalprices/?after=${data.after}&before=${data.before}&key=${data.selectedMetal}&preview=${getPreviewData}`)
			.then(res => {
				let response = res.data
				const trend = getTrend(response.prices, 'price');

				if (getPreviewData) {
					setData({
						...data,
						prices: response.prices,
						previewData: response.preview,
						trend,
						isLoading: false,
					});
				} else {
					setData({
						...data,
						prices: response.prices,
						trend,
						isLoading: false,
					});
				}
			})
			.catch(err => {
				console.error(err)
				setData({
					...data,
					prices: [],
					previewData: [],
					trend: 0,
					isLoading: false,
				});
			})
	};

	useEffect(() => {
		getData();
	}, [data.after, data.before, data.selectedRange]);

	useEffect(() => {
		getData({getPreviewData: true});
	}, [data.selectedMetal]);

	useEffect(() => {
		getData({getPreviewData: true});
	}, []);

	useEffect(() => {
		if (data.prices?.length) {
			setDataInRange(getDataInRange(data.prices, 'post_date', data.after, data.before));
		}
	}, [data.prices, data.selectedRange]);

	const handleStartDate = (newDate) => {
		if (newDate >= data.before) {
			error = "Invalid Date";
		} else {
			setData({
				...data,
				after: dayjs(newDate),
			});
		}
	};

	const handleEndDate = (newDate) => {
		if (newDate <= data.after) {
			error = "Invalid Date";
		} else {
			setData({
				...data,
				before: dayjs(newDate),
			});
		}
	};

	return (
		<div className={styles.container}>
			{dataInRange.length ?
				<>
					<div className={styles.datePicker}>
						<LocalizationProvider adapterLocale={document.documentElement.lang === 'de-DE' ? de : enGB} dateAdapter={AdapterDateFns}>
							<DatePicker
								label=""
								format="dd/MM/yyyy"
								value={data.after.toDate()}
								onChange={handleStartDate}
								disableFuture={true}
								minDate={dayjs('2021-01-01')}
								// renderInput={(params) => <TextField
								// 	{...params}
								// 	error={error !== '' && error} />}
							/>
							<div>-</div>
							<DatePicker
								label=""
								format="dd/MM/yyyy"
								value={data.before.toDate()}
								onChange={handleEndDate}
								disableFuture={true}
								minDate={dayjs('2021-01-14')}
								// renderInput={(params) => <TextField
								// 	{...params}
								// 	error={error !== '' && error} />}
							/>
						</LocalizationProvider>
					</div>
					<div className={styles.values}>
						<div className={styles.currentPrice}>{numberToEuro((dataInRange.length <= 2) ? dataInRange[dataInRange.length - 1].price : getAverage(dataInRange), data.selectedMetalUnit)}</div>
						<div>
							<div className={styles.highlow}><span>HIGH:</span> <span className={styles.highlow__price}>{numberToEuro(Math.max(...dataInRange.map((item) => item.price)), data.selectedMetalUnit)}</span></div>
							<div className={styles.highlow}><span>LOW:</span> <span className={styles.highlow__price}>{numberToEuro(Math.min(...dataInRange.map((item) => item.price)), data.selectedMetalUnit)}</span></div>
						</div>
						<div className={styles.priceChange} style={{color: getBaseColor(getTrend(dataInRange))}}>{formatPercentage(getTrend(dataInRange) / dataInRange[dataInRange.length - 1].price * 100)}</div>
					</div>
					<div style={{width: '100%', height: 240}}>
						<Chart data={dataInRange} trend={getTrend(dataInRange)} range={data.selectedRange} />
					</div>
				</>
			: null}
		</div>
	);
};

export default ChartWrapper;
