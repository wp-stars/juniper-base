import {AreaChart, Area, Tooltip, ResponsiveContainer} from 'recharts';
import {formatDatedmY, getBaseColor, numberToEuro} from '../functions';
import {useContext} from 'react';
import {DataContext} from '../DataProvider';
import styles from './chart.module.css';

const CustomTooltip = ({active, payload}) => {
	const {data, setData} = useContext(DataContext);

	if (active && payload && payload.length) {
		return (
			<div className={styles.tooltip}>
				<div className={styles.tooltip__price}>{numberToEuro(payload[0].value, data.selectedMetalUnit)}</div>
				<div className={styles.tooltip__date}>{formatDatedmY(new Date(payload[0].payload.post_date))}</div>
			</div>
		);
	}
	return null;
};

const Chart = ({data, trend = 0, small = false}) => {
	let gradientId = 'gradientEqual';
	if (trend > 0) {
		gradientId = 'gradientPlus';
	} else if (trend < 0) {
		gradientId = 'gradientMinus';
	}
	//override - always grey for non-interactive
	if (small) {
		gradientId = 'gradientEqual';
	}
	const baseColor = getBaseColor(trend, small);

	return (
		<ResponsiveContainer>
			<AreaChart data={data} margin={{top: 0, right: 0, left: 0, bottom: 0}}>
				<defs>
					<linearGradient id="gradientEqual" x1="0" y1="0" x2="0" y2="1">
						<stop offset="5%" stopColor="var(--color-grey_medium)" stopOpacity={0.7}/>
						<stop offset="95%" stopColor="var(--color-grey_medium)" stopOpacity={0.1}/>
					</linearGradient>
					<linearGradient id="gradientPlus" x1="0" y1="0" x2="0" y2="1">
						<stop offset="5%" stopColor="var(--color-iwg_category_red)" stopOpacity={0.7}/>
						<stop offset="95%" stopColor="var(--color-iwg_category_red)" stopOpacity={0.1}/>
					</linearGradient>
					<linearGradient id="gradientMinus" x1="0" y1="0" x2="0" y2="1">
						<stop offset="5%" stopColor="var(--color-iwg_category_green)" stopOpacity={0.7}/>
						<stop offset="95%" stopColor="var(--color-iwg_category_green)" stopOpacity={0.1}/>
					</linearGradient>
				</defs>
				{!small && <Tooltip content={<CustomTooltip />} animationDuration={300} />}
				<Area animationDuration={300} dataKey="price" stroke={baseColor} strokeWidth={small ? 3 : 5} fillOpacity={1} fill={`url(#${gradientId})`} />
			</AreaChart>
		</ResponsiveContainer>
	);
};

export default Chart;
