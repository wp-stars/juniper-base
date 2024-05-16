import React, {useContext} from 'react';
import {DataContext} from '../DataProvider';
import styles from './selection.module.css';
const {metals} = wpVars;

const Selection = () => {
	const {data, setData} = useContext(DataContext);

	const onMetalSelect = (evt) => {
		evt.preventDefault();
		if (!data.isLoading) {
			setData({
				...data,
				selectedMetal: evt.target.dataset.key,
				selectedMetalUnit: evt.target.dataset.unit,
			});
		}
	};

	return (
		<div className={styles.container}>
			{metals && metals.length && metals.map((elem) => {
				return (
					<div className={[styles.metal, (data.selectedMetal === elem.key) && 'active'].join(' ')} key={elem.key} data-key={elem.key} data-unit={elem.unit} onClick={onMetalSelect}>
						<div className={styles.logo}>
							{elem.short}
							<span className={styles.number}>{elem.number}</span>
						</div>
						<div className={styles.label}>{elem.label}</div>
					</div>
				);
			})}
		</div>
	);
};

export default Selection;
