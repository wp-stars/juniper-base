import dayjs from 'dayjs'

/**
 * format a date to YYYY-MM-DD
 * @param value
 * @returns string
 */
export const formatDateYYYYMMDD = (value) => {
	const dateString = new Date (value);
	const month = (dateString.getMonth() + 1) > 9 ? (dateString.getMonth() + 1) : (`0${(dateString.getMonth() + 1)}`);
	const date = (dateString.getDate()) > 9 ? (dateString.getDate()) : (`0${dateString.getDate()}`);
	return `${dateString.getFullYear()}-${month}-${date}`;
};

/**
 * format a date to d.m.Y
 * @param value
 * @returns string
 */
export const formatDatedmY = (value) => {
	const dateString = new Date (value);
	const month = (dateString.getMonth() + 1) > 9 ? (dateString.getMonth() + 1) : (`0${(dateString.getMonth() + 1)}`);
	const date = (dateString.getDate()) > 9 ? (dateString.getDate()) : (`0${dateString.getDate()}`);
	return `${date}.${month}.${dateString.getFullYear()}`;
};

/**
 * format a number to euro, including euro sign
 * @param value
 * @param unit
 * @returns string
 */
export const numberToEuro = (value, unit = '€/g') => {
	return value.toLocaleString('de-AT', {style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + unit;
};

/**
 * turn a number into formatted percentage
 * @param value
 * @returns string
 */
export const formatPercentage = (value) => {
	return `${(value < 0) ? '-' : '+'} ${Math.abs(value).toLocaleString('de-AT', {minimumFractionDigits: 2, maximumFractionDigits: 2})} %`;
};

/**
 * fill zero values in basic strings
 * @param value
 * @returns string
 */
export const zeroFill = (value) => {
	if (value <= 9) {
		return `0${value}`;
	}
	return `${value}`;
};

/**
 * get trend from data array
 * @param data
 * @param key
 * @returns number
 */
export const getTrend = (data, key = 'price') => {
	return (data?.length >= 2) ? data[data.length - 1][key] - data[0][key] : 0;
};

/**
 * get base color depending on trend
 * @param trend positive or negative trend
 * @param small size of chart
 * @returns string trend color
 */
export const getBaseColor = (trend, small = false) => {
	let baseColor = 'var(--color-grey_medium)';
	if (small) {
		return baseColor;
	}
	if (trend < 0) {
		baseColor = 'var(--color-iwg_category_green)';
	} else if (trend > 0) {
		baseColor = 'var(--color-iwg_category_red)';
	}
	return baseColor;
};

/**
 * get data in specific time range (current date minus range in days)
 * @param data
 * @param dataKey
 * @param range
 * @returns []
 */
export const getDataInRange = (data, dataKey, after, before) => {
	let dataInRange = [];
	const afterTime = after.unix();
	const beforeTime = before.unix();
	dataInRange = data.filter((elem) => {
		return (dayjs(elem[dataKey]).unix() >= afterTime && dayjs(elem[dataKey]).unix() <= beforeTime);
	});
	//no data, so just return the last 2 entries instead, to get a trend
	if (dataInRange.length <= 2 && data.length > 2) {
		return data.slice(-2);
	}

	return dataInRange;
};

/**
 * get average value of array of objects
 * @param data
 * @param dataKey
 * @returns number
 */
export const getAverage = (data, dataKey = 'price') => {
	return data.reduce((previousValue, currentValue) => {
		return previousValue + currentValue[dataKey];
	}, 0) / data.length;
};
