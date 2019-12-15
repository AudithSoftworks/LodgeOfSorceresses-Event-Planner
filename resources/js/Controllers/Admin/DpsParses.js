import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "pending-parses-scss" */
    '../../../sass/_pending_dps_parses.scss'
);

import { faUserCheck, faUserTimes } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';
import { errorsAction, infosAction, successAction } from '../../actions/notifications';
import { deletePendingDpsParse, getPendingDpsParses, updatePendingDpsParse } from '../../vendor/api/admin';
import axios from '../../vendor/axios';
import Loading from '../../Components/Loading';
import Notification from '../../Components/Notification';

class DpsParses extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            dpsParses: null,
        };
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Request cancelled.');
    };

    componentDidMount = () => {
        this.cancelTokenSource = axios.CancelToken.source();
        getPendingDpsParses(this.cancelTokenSource)
            .then(dpsParses => {
                this.cancelTokenSource = null;
                this.setState({ dpsParses });
            })
            .catch(error => {
                if (!axios.isCancel(error)) {
                    const message = error.response.data.message || error.response.statusText || error.message;
                    this.props.dispatch(errorsAction(message));
                }
            });
    };

    handleDisapprove = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to **disapprove** this parse?')) {
            this.cancelTokenSource = axios.CancelToken.source();
            const reasonForDisapproval = window.prompt('Please provide a reason for disapproval. This will be posted on Discord.');
            if (reasonForDisapproval && reasonForDisapproval.length) {
                const currentTarget = event.currentTarget;
                const { dpsParses } = this.state;
                const parseId = parseInt(currentTarget.getAttribute('data-id'));
                deletePendingDpsParse(this.cancelTokenSource, parseId, reasonForDisapproval)
                    .then(response => {
                        this.cancelTokenSource = null;
                        if (response === true) {
                            delete dpsParses.entities.dpsParses[parseId];
                            dpsParses.result = dpsParses.result.filter(value => value !== parseId);
                            this.setState({ dpsParses });
                            const message = 'Parse disapproved.';
                            this.props.dispatch(successAction(message));
                        }
                    })
                    .catch(error => {
                        if (!axios.isCancel(error)) {
                            const message = error.response.data.message || error.response.statusText || error.message;
                            this.props.dispatch(errorsAction(message));
                        }
                    });
            }
        }
    };

    handleApprove = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to **approve** this parse?')) {
            this.cancelTokenSource = axios.CancelToken.source();
            const currentTarget = event.currentTarget;
            const parseId = parseInt(currentTarget.getAttribute('data-id'));
            const { dpsParses } = this.state;
            updatePendingDpsParse(this.cancelTokenSource, parseId)
                .then(response => {
                    this.cancelTokenSource = null;
                    if (response.data) {
                        delete dpsParses.entities.dpsParses[parseId];
                        dpsParses.result = dpsParses.result.filter(value => value !== parseId);
                        this.setState({ dpsParses });
                        const message = response.data.message;
                        this.props.dispatch(successAction(message));
                    }
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        this.props.dispatch(errorsAction(message));
                    }
                });
        }
    };

    renderListItem(dpsParse) {
        const parseSets = dpsParse.sets.map(set => (
            <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className="badge badge-dark">
                {set['name']}
            </a>
        ));
        dpsParse.actionList = {
            approve: (
                <Link to="" onClick={this.handleApprove} data-id={dpsParse['id']} title="Approve this Parse">
                    <FontAwesomeIcon icon={faUserCheck} />
                </Link>
            ),
            disapprove: (
                <Link to="" onClick={this.handleDisapprove} data-id={dpsParse['id']} title="Disapprove this Parse">
                    <FontAwesomeIcon icon={faUserTimes} />
                </Link>
            ),
        };
        const actionListRendered = [];
        for (const [actionType, link] of Object.entries(dpsParse.actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }

        return (
            <tr key={'dpsParseRow-' + dpsParse['id']}>
                <td title={dpsParse['owner']['name']}>{dpsParse['owner']['name']}</td>
                <td title={dpsParse['character']['name']}>
                    {dpsParse['character']['name']}
                    <br />
                    <small>
                        {dpsParse['character']['class']} / {dpsParse['character']['role']}
                    </small>
                </td>
                <td>{parseSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                <td className="text-right">{dpsParse['dps_amount']}</td>
                <td className="text-right">
                    <a href={dpsParse['parse_file_hash']['large']} target="_blank">
                        <img src={dpsParse['parse_file_hash']['thumbnail']} alt="Parse screenshot" />
                    </a>
                </td>
                <td className="text-right">
                    <a href={dpsParse['superstar_file_hash']['large']} target="_blank">
                        <img src={dpsParse['superstar_file_hash']['thumbnail']} alt="Superstar screenshot" />
                    </a>
                </td>
                <td>
                    <ul className="action-list">{actionListRendered}</ul>
                </td>
            </tr>
        );
    }

    renderList = dpsParses => {
        let parsesRendered = dpsParses.result.map(itemId => {
            const dpsParse = dpsParses.entities.dpsParses[itemId];
            return this.renderListItem(dpsParse);
        });
        if (parsesRendered.length) {
            parsesRendered = [
                <table key="pending-dps-parses-table" className="pl-2 pr-2 col-md-24">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Character</th>
                            <th scope="col">Sets</th>
                            <th scope="col" style={{ textAlign: 'right' }}>
                                DPS
                            </th>
                            <th scope="col" style={{ textAlign: 'right' }}>
                                Parse Screenshot
                            </th>
                            <th scope="col" style={{ textAlign: 'right' }}>
                                Superstar Screenshot
                            </th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>,
            ];
        }

        return [
            <section className="col-md-24 p-0 mb-4" key="dpsParseList">
                <h2 className="form-title col-md-24">Parses Pending Approval</h2>
                <article className="alert-info">
                    <b>DPS Parse Approval Checklist</b>
                    <ul style={{ listStyleType: 'circle' }}>
                        <li>Do the Characters on both screenshots have the same name as the Character listed?</li>
                        <li>Does Parse screenshot have the same DPS amount as it is listed in this table?</li>
                        <li>Is parse in the screenshot the same Role (Stamina vs Magicka) as the Character listed?</li>
                        <li>Is the gear listed in Superstar screenshot the same as in the Character listed?</li>
                    </ul>
                    If any of these fail, please Reject the Parse by clicking <FontAwesomeIcon icon={faUserTimes} /> icon, and by stating the reason.
                </article>
                {parsesRendered}
            </section>,
        ];
    };

    renderNoPendingDpsParsesNotification = dpsParses => {
        const { dispatch, notifications } = this.props;
        if (dpsParses && !dpsParses.result.length && notifications.find(n => n.key === 'admin-no-pending-dps-parses') === undefined) {
            const message = 'No pending Parses found!';
            dispatch(
                infosAction(
                    message,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                        width: 250,
                    },
                    'admin-no-pending-dps-parses'
                )
            );
        }
    };

    render = () => {
        const { dpsParses } = this.state;
        if (!dpsParses) {
            return [<Loading message="Fetching the list of pending DPS parses..." key="loading" />, <Notification key="notifications" />];
        }
        this.renderNoPendingDpsParsesNotification(dpsParses);

        return [...this.renderList(dpsParses), <Notification key="notifications" />];
    };
}

DpsParses.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    notifications: state.getIn(['notifications']),
});

export default connect(mapStateToProps)(DpsParses);
