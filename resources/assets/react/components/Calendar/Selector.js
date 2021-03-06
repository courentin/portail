import React from 'react';
import { Button } from 'reactstrap';

import { colorFromBackground } from '../../utils';

export default class CalendarSelector extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			selectedCalendars: props.selectedCalendars || {},
		};
	}

	generateCalendar(calendar) {
		const { loadingCalendars } = this.props;
		const { selectedCalendars } = this.state;
		const props = {
			style: {
				color: colorFromBackground(calendar.color),
				backgroundColor: calendar.color,
			},
		};

		if (loadingCalendars[calendar.id]) {
			props.disabled = true;
		} else if (Object.keys(selectedCalendars).includes(calendar.id)) {
			props.onClick = () => this.removeCalendar(calendar);
		} else {
			props.onClick = () => this.addCalendar(calendar);
			delete props.style;
		}

		return (
			<Button key={calendar.id} className="btn-sm ml-2 mb-1" {...props}>
				{calendar.name}
			</Button>
		);
	}

	addCalendar(calendar) {
		this.setState(
			prevState => {
				prevState.selectedCalendars[calendar.id] = calendar;

				return prevState;
			},
			() => {
				const { onAddCalendar } = this.props;

				if (onAddCalendar) {
					onAddCalendar(calendar);
				}
			}
		);
	}

	removeCalendar(calendar) {
		this.setState(
			prevState => {
				delete prevState.selectedCalendars[calendar.id];

				return prevState;
			},
			() => {
				const { onRemoveCalendar } = this.props;

				if (onRemoveCalendar) {
					onRemoveCalendar(calendar);
				}
			}
		);
	}

	render() {
		const { calendars } = this.props;

		return (
			<div className="p-3">
				Calendriers: {Object.values(calendars).map(calendar => this.generateCalendar(calendar))}
			</div>
		);
	}
}
