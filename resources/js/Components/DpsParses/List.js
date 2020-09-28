import(/* webpackPrefetch: true, webpackChunkName: "dps-parses-scss" */ '../../../sass/_my_dps_parses.scss');

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { dpsParses } from '../../vendor/data';
import Item from './Item';

class List extends PureComponent {
    render = () => {
        const { dpsParses, onDeleteHandler } = this.props;
        let parsesRendered = dpsParses.map(dpsParse => <Item key={dpsParse.id} dpsParse={dpsParse} onDeleteHandler={onDeleteHandler} />);
        if (parsesRendered.length) {
            parsesRendered = (
                <table key="dps-parses-table" className="dps-parses-table pl-2 pr-2 col-md-24">
                    <thead>
                        <tr>
                            <th scope="col">Sets</th>
                            <th scope="col">DPS Number</th>
                            <th scope="col">Parse Screenshot</th>
                            <th scope="col">Info Screenshot</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>
            );
        }

        return parsesRendered;
    };
}

List.propTypes = {
    dpsParses,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
};

export default List;
