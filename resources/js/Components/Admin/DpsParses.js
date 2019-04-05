import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faThList, faUserCheck, faUserEdit, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Fragment, PureComponent } from 'react';
import { Link } from "react-router-dom";
import Notification from '../Notification';
import Axios from '../../vendor/Axios';
import Loading from "../Loading";

library.add(faSpinner, faTachometerAlt, faThList, faUserCheck, faUserEdit, faUserPlus);

class DpsParses extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            parsesLoaded: false,
            dpsParses: [],
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();

        Axios.get('/api/admin/parses', {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            this.cancelTokenSource = null;
            if (response.data) {
                this.setState({
                    parsesLoaded: true,
                    dpsParses: response.data.dpsParses,
                    messages: [
                        {
                            type: "success",
                            message: "Parses loaded."
                        }
                    ]
                });
            }
        }).catch(error => {
            if (!Axios.isCancel(error)) {
                this.setState({
                    messages: [
                        {
                            type: "danger",
                            message: error.response.data.message || error.response.statusText
                        }
                    ]
                })
            }
            if (error.response && error.response.status === 403) {
                this.props.history.push('/', this.state);
            }
        });
    };

    componentWillUnmount() {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    handleDelete = (event) => {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this parse?')) {
            this.cancelTokenSource = Axios.CancelToken.source();
            let currentTarget = event.currentTarget;
            const dpsParses = this.state.dpsParses;
            Axios.delete('/api/chars/' + this.props.match.params.id + '/parses/' + currentTarget.getAttribute('data-id'), {
                cancelToken: this.cancelTokenSource.token,
            }).then((response) => {
                this.cancelTokenSource = null;
                if (response.status === 204) {
                    dpsParses.forEach((item, idx) => {
                        if (item.id === parseInt(currentTarget.getAttribute('data-id'))) {
                            delete (dpsParses[idx]);
                        }
                    });
                    this.setState({
                        dpsParses: dpsParses,
                        messages: [
                            {
                                type: "success",
                                message: 'DPS Parse deleted.'
                            }
                        ],
                    });
                }
            }).catch(error => {
                if (!Axios.isCancel(error)) {
                    this.setState({
                        messages: [
                            {
                                type: "danger",
                                message: error.response.data.message || error.response.statusText
                            }
                        ]
                    })
                }
            });
        }
    };

    renderList = (dpsParses) => {
        let parsesRendered = dpsParses.map(
            item => {
                const characterSets = item.sets.map(set => <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className='badge badge-dark'>{set['name']}</a>);
                item.actionList = {
                    approve: <Link to='' onClick={this.handleDelete} data-id={item.id} title='Approve this Parse'><FontAwesomeIcon icon="user-check"/></Link>
                };
                let actionListRendered = [];
                for (const [actionType, link] of Object.entries(item.actionList)) {
                    actionListRendered.push(<li key={actionType}>{link}</li>);
                }

                return (
                    <tr key={'dpsParseRow-' + item.id}>
                        <td title={item.owner.name}>{item.owner.name}</td>
                        <td title={item.character.name}>{item.character.name}</td>
                        <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                        <td className='text-right'>{item['dps_amount']}</td>
                        <td className='text-right'>
                            <a href={item['parse_file_hash']['large']} target='_blank'>
                                <img src={item['parse_file_hash']['thumbnail']} alt='Parse screenshot'/>
                            </a>
                        </td>
                        <td className='text-right'>
                            <a href={item['superstar_file_hash']['large']} target='_blank'>
                                <img src={item['superstar_file_hash']['thumbnail']} alt='Superstar screenshot'/>
                            </a>
                        </td>
                        <td>
                            <ul className='actionList'>{actionListRendered}</ul>
                        </td>
                    </tr>
                )
            }
        );
        if (parsesRendered.length) {
            parsesRendered = [
                <table key="character-list-table" className='pl-2 pr-2 col-md-24'>
                    <thead>
                        <tr>
                            <th style={{width: '10%'}}>User</th>
                            <th style={{width: '20%'}}>Character</th>
                            <th style={{width: '25%'}}>Sets</th>
                            <th style={{width: '10%', textAlign: 'right'}}>DPS Number</th>
                            <th style={{width: '15%', textAlign: 'right'}}>Parse Screenshot</th>
                            <th style={{width: '15%', textAlign: 'right'}}>Superstar Screenshot</th>
                            <th style={{width: '5%'}}/>
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>
            ];
        } else {
            const messages = [
                {
                    type: 'default',
                    message: [
                        <Fragment key='f-1'>Create a new parse, by clicking</Fragment>,
                        <FontAwesomeIcon icon="user-plus" key='icon'/>,
                        <Fragment key='f-2'>icon on top right corner.</Fragment>
                    ].reduce((prev, curr) => [prev, ' ', curr])
                }
            ];

            const options = {
                container: 'bottom-center',
                animationIn: ["animated", "bounceInDown"],
                animationOut: ["animated", "bounceOutDown"],
                dismiss: {duration: 30000},
            };

            parsesRendered = [
                <Notification key='notifications' messages={messages} options={options}/>
            ];
        }

        return [
            <section className="col-md-24 p-0 mb-4" key='dpsParseList'>
                <h2 className="form-title col-md-24">Parses</h2>
                {parsesRendered}
            </section>
        ];
    };

    render = () => {
        const {parsesLoaded, dpsParses, messages} = this.state;
        if (!parsesLoaded) {
            return [
                <Loading key='loading'/>,
                <Notification key='notifications' messages={messages}/>
            ]
        }

        return [
            this.renderList(dpsParses),
            <Notification key='notifications' messages={messages}/>,
        ]
    };
}

export default DpsParses;
