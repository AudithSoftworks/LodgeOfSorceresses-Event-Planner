import { library } from '@fortawesome/fontawesome-svg-core';
import { faTrashAlt } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Link } from 'react-router-dom';
import { renderActionList } from '../../helpers';
import { dpsParse } from '../../vendor/data';

library.add(faTrashAlt);

class Item extends PureComponent {
    render = () => {
        const { dpsParse, onDeleteHandler } = this.props;
        const parseSets = dpsParse.sets.map(set => (
            <a key={set.id} href={'https://eso-sets.com/set/' + set.id} className='badge badge-dark'>
                {set.name}
            </a>
        ));

        dpsParse.actionList = {
            delete:
                typeof onDeleteHandler === 'function' ? (
                    <Link to="#" onClick={onDeleteHandler} data-id={dpsParse.id} title='Delete this Parse'>
                        <FontAwesomeIcon icon={['far', 'trash-alt']} />
                    </Link>
                ) : null,
        };

        return (
            <tr key={'dpsParseRow-' + dpsParse.id}>
                <td>{parseSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                <td>{dpsParse.dps_amount}</td>
                <td>
                    <a href={dpsParse.parse_file_hash.large} target='_blank' rel='noreferrer'>
                        <img src={dpsParse.parse_file_hash.thumbnail} alt='Parse screenshot' />
                    </a>
                </td>
                <td>
                    <a href={dpsParse.info_file_hash.large} target='_blank' rel='noreferrer'>
                        <img src={dpsParse.info_file_hash.thumbnail} alt='Info screenshot' />
                    </a>
                </td>
                <td>
                    <ul className="action-list">{renderActionList(dpsParse.actionList)}</ul>
                </td>
            </tr>
        );
    };
}

Item.propTypes = {
    dpsParse,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button
};

export default Item;
