const { render } = wp.element; 
import Selection from './components/selection';
import Range from './components/range';
import ChartWrapper from './components/chartWrapper';
import {DataProvider} from './DataProvider';
import './styles.css'

const Main = () => {
	return (
		<DataProvider>
			<Selection />
			<Range />
			<ChartWrapper />
		</DataProvider>
	);
};


if (document.getElementById('reactNewMetalprices')) { 
  render(<Main />, document.getElementById('reactNewMetalprices'));
}