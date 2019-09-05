import { library } from '@fortawesome/fontawesome-svg-core';
import { faTrashAlt } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Link } from 'react-router-dom';
import { character, dpsParse } from '../../vendor/data';

library.add(faTrashAlt);

class Item extends PureComponent {
    render = () => {
        const { character, dpsParse, onDeleteHandler } = this.props;

        const characterSets = character.sets.map(set => (
            <a key={set.id} href={'https://eso-sets.com/set/' + set.id} className="badge badge-dark">
                {set.name}
            </a>
        ));

        dpsParse.actionList = {
            delete:
                typeof onDeleteHandler === 'function' ? (
                    <Link to="#" onClick={onDeleteHandler} data-id={dpsParse.id} title="Delete this Parse">
                        <FontAwesomeIcon icon={['far', 'trash-alt']} />
                    </Link>
                ) : null,
        };
        const actionListRendered = [];
        for (const [actionType, link] of Object.entries(dpsParse.actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }

        return (
            <tr key={'dpsParseRow-' + dpsParse.id}>
                <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                <td>{dpsParse.dps_amount}</td>
                <td>
                    <a href={dpsParse.parse_file_hash.large} target="_blank">
                        <img src={dpsParse.parse_file_hash.thumbnail} alt="Parse screenshot" />
                    </a>
                </td>
                <td>
                    <a href={dpsParse.superstar_file_hash.large} target="_blank">
                        <img src={dpsParse.superstar_file_hash.thumbnail} alt="Superstar screenshot" />
                    </a>
                    <ul className="actionList">{actionListRendered}</ul>
                </td>
            </tr>
        );
    };
}

Item.propTypes = {
    character,
    dpsParse,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button
};

export default Item;
