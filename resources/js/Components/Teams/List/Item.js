import { faPortrait, faTrashAlt, faUserEdit } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Link } from 'react-router-dom';
import { renderActionList } from "../../../helpers";
import { team } from '../../../vendor/data';

class Item extends PureComponent {
    render = () => {
        const { authorizedTeamManager, team, deleteTeamHandler } = this.props;
        team.actionList = {
            view: (
                <Link to={'/teams/' + team.id} title="Team Details">
                    <FontAwesomeIcon icon={faPortrait} />
                </Link>
            ),
            edit:
                authorizedTeamManager ? (
                    <Link to={'/teams/' + team.id + '/edit'} title="Edit Team">
                        <FontAwesomeIcon icon={faUserEdit} />
                    </Link>
                ) : null,
            delete:
                typeof deleteTeamHandler === 'function' && authorizedTeamManager ? (
                    <Link to="#" onClick={deleteTeamHandler} data-id={team.id} title="Delete Team">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        return (
            <tr className={'tier-' + team.tier} key={'teamRow-' + team.id} data-id={team.id}>
                <td>{team.name}</td>
                <td>{'Tier-' + team.tier}</td>
                <td>{'@' + team.led_by.name}</td>
                <td>{team.members.length + ' characters'}</td>
                <td>
                    <ul className="action-list">{renderActionList(team.actionList)}</ul>
                </td>
            </tr>
        );
    };
}

Item.propTypes = {
    authorizedTeamManager: PropTypes.bool.isRequired,
    team: team.isRequired,
    deleteTeamHandler: PropTypes.func, // based on existense of this param, we render Delete button
};

export default Item;
