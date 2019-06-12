import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import { amIAdmin } from '../../helpers';
import { characters, user } from '../../vendor/data';
import Notification from '../../Components/Notification';

class Home extends PureComponent {
    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        if (!amIAdmin(this.props)) {
            return history.push('/');
        }

        return [
            <section className="col-md-24 p-0 mb-4" key="characterList">
                <h2 className="form-title col-md-24">Dashboard</h2>
                <article className="col-md-24">
                    <h3>Available actions</h3>
                    <ul>
                        <li>
                            <Link to="/admin/characters" title="Character List">
                                Character List
                            </Link>
                        </li>
                        <li>
                            <Link to="/admin/parses" title="Approve Parses">
                                DPS Parses pending Approval
                            </Link>
                        </li>
                    </ul>
                </article>
                <Notification key="notifications" />
            </section>,
        ];
    };
}

Home.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    notifications: PropTypes.array,

    allCharacters: characters,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
    notifications: state.getIn(['notifications']),

    allCharacters: state.getIn(['allCharacters']),
});

export default connect(mapStateToProps)(Home);
