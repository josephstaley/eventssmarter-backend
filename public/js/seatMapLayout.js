(function ($) {
    function Movieseat(_paramObject) {
        var maxSeat, minSeat, pluginOptions = $.extend({},_paramObject.options);
        var getMainLayoutFunc = function (_obj) {
            maxSeat = _obj.intMaxSeatId;
            minSeat = _obj.intMinSeatId;
            var _html = "<div class='seat-selection' style='width:" + (maxSeat + 2) * 19 + "px;float:left;'>";
            for (var i = 0; i < _obj.objArea.length; i++) {
                _html += getAreaLayoutFunc(_obj.objArea[i]);
            }
            var actionPanel = pluginOptions.showActionButtons?"<div class='seat-proccess-panel'> <button  type='button' class='layout-action-btn layout-btn-cancel  "+ pluginOptions.classes.cancelBtn +"'> Cancel </button> <button type='button' class='layout-action-btn layout-btn-done "+ pluginOptions.classes.doneBtn +"'> Done </button> </div>":"";
            if (pluginOptions.stageShow)
            _html += "<div class='movie-screen "+ pluginOptions.classes.screen +"'>-- Stage --</div>"+ actionPanel +"</div>";
            return _html;
        }
        var getAreaLayoutFunc = function (_obj) {
            var _html = "<div class='seat-area "+ pluginOptions.classes.area +"'>";
            _html += "<div class='seat-area-desc'>" + _obj.AreaDesc + "</div>";
            for (var i = 0; i < _obj.objRow.length; i++) {
                _html += getRowLayoutFunc(_obj.objRow[i], _obj);
            }
            _html += "</div>";
            return _html;
        }
        var getRowLayoutFunc = function (_obj, _area) {
            var _html = "<ul class='seat-area-row "+ pluginOptions.classes.row +"'> <li class='seat-row-seat row-indicator'>" + _obj.PhyRowId + "</li>";
            var totalSpace = 0;
            for (var i = 0; i < _obj.objSeat.length; i++) {
                if (i == 0) {
                    var startSpace = _obj.objSeat[i].GridSeatNum - minSeat - (_obj.objSeat[i].GridSeatNum - _obj.objSeat[i].seatNumber);
                    if (startSpace > 0) {
                        for (var k = 0; k < startSpace; k++) {
                            _html += getSeatLayoutFunc({}, false, _obj, _area);
                        }
                    }
                }
                var extraSpace = parseInt(_obj.objSeat[i].GridSeatNum - (_obj.objSeat[i].seatNumber + totalSpace));
                if (extraSpace > 0) {
                    totalSpace += extraSpace;
                    for (var j = 0; j < extraSpace; j++) {
                        _html += getSeatLayoutFunc({}, false, _obj, _area);
                    }
                }
                _html += getSeatLayoutFunc(_obj.objSeat[i], true, _obj, _area);
            }
            var endSpace = maxSeat - (_obj.objSeat.length + startSpace + totalSpace)
            if (endSpace > 0) {
                for (var l = 0; l < endSpace; l++) {
                    _html += getSeatLayoutFunc({}, false, _obj, _area);
                }
            }
            _html += "</ul>";
            return _html;
        }
        getSeatLayoutFunc = function (_obj, isSeat, _row, _area) {
            _obj.GridRowId = _row.GridRowId;
            _obj.PhyRowId = _row.PhyRowId;
            _obj.AreaNum = _area.AreaNum;
            _obj.AreaCode = _area.AreaCode;
            _obj.AreaDesc = _area.AreaDesc;
            _obj = _paramObject.callSeatRender(_obj);
            if (_obj) {
                var dataString = JSON.stringify(_obj);
                var classes = pluginOptions.classes.seat;
                if (_obj.SeatStatus !== null && isSeat) {
                    let tooltipText = ''
                    classes += " can-select tooltip-text ";
                    let seatText = _obj.seatText ? _obj.seatText: _obj.seatNumber
                    let seatSection = _obj.section ? 'Section: ' + _obj.section: ''
                    tooltipText = "<span class='tooltiptext'>" + _obj.AreaDesc + "<br>" + seatSection + "ROW " + _obj.PhyRowId + ",  Seat " + seatText + "</span></span>"
                    _paramObject.options.voucher.some(seat =>{
                        if (_obj.AreaNum === seat.AreaNum && _obj.GridRowId === seat.GridRowId && _obj.seatNumber === seat.seatNumber){
                            classes += " current-selected ";
                            return true
                        }
                    })
                    return "<li data-seatdefination='" + dataString + "' class='seat-row-seat seat-yes " + classes + " '><span>" + seatText + "</span>" + tooltipText +"</li>";
                } else {
                    return "<li class='seat-row-seat " + classes + "'></li>";
                }
            }
        }
        return {
            getAreaLayout: getAreaLayoutFunc,
            getRowLayout: getRowLayoutFunc,
            getSeatLayout: getSeatLayoutFunc,
            getMainLayout: getMainLayoutFunc
        }
    }
    $.fn.seatLayout = function (_options) {
        var _optionsObj = $.extend(true,{},{
            type:'movie',
            showActionButtons:true,
            classes : {
                doneBtn : '',
                cancelBtn : '',
                seat:'',
                row:'',
                area:'',
                screen:''
            },
            numberOfSeat:0,
            stageShow: true
        },_options);
        var selectedSeats = [];
        var nuberOfSeat = _optionsObj.numberOfSeat;
        var tempSelected = 0;
        var _el = this;
        var _html = "";
        var seatInstance = new Movieseat({
            callSeatRender: function (_seatObj) {
                if (_optionsObj.callOnSeatRender) {
                    return _optionsObj.callOnSeatRender(_seatObj);
                }
                return _seatObj;
            },
            options : _optionsObj
        });
        _html = seatInstance.getMainLayout(_optionsObj.data.seatLayout.colAreas);
        _el.html(_html);

        _el.find('.layout-btn-done').prop('disabled', true);
        _el.find('.layout-btn-done').click(function (e) {
            if (_optionsObj.selectionDone) {
                _optionsObj.selectionDone({ "selected": selectedSeats });
            }
        });
        _el.find('.layout-btn-cancel').click(function (e) {
            returnFlag = true;
            if (_optionsObj.cancel) {
                returnFlag = _optionsObj.cancel(e) == false ? false : true;
            }
            if (returnFlag) {
                $(_el).remove();
                seatInstance = null;
                _optionsObj = null;
            }
        });
        return this;
    };
})(jQuery);
