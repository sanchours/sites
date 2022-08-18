import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";
import * as sk from '../../../../../../services/_sk/api'

export const handler = (props) => {

  const {record, mainContainerData, dispatch} = props;

  if ( record.no_edit === "1" ){
    return null;
  }

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'FieldEdit',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'FieldEdit',
          data: {
            id: record.id
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });

};

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  handler(props);

};

export default (props) => {

  const {record, configButton} = props;

  const {tooltip} = configButton;

  if (record.no_edit === "1")
    return null;

  return (
    <Tooltip
      placement="bottomRight"
      title={tooltip}
    >
      <span>
        <Icon
          alias='icon-edit'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};
