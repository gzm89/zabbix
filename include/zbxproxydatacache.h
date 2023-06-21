/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

#ifndef ZABBIX_ZBXPROXYDATACACHE_H
#define ZABBIX_ZBXPROXYDATACACHE_H

#include "zbxalgo.h"
#include "zbxtime.h"
#include "zbxjson.h"

typedef struct
{
	zbx_uint64_t	mem_used;
	zbx_uint64_t	mem_total;
}
zbx_pdc_mem_info_t;

typedef struct
{
	int		state;
	zbx_uint64_t	changes_num;
}
zbx_pdc_state_info_t;

int	zbx_pdc_init(zbx_uint64_t size, int age, int offline_buffer, char **error);

void	zbx_pdc_update_state(int more);
void	zbx_pdc_flush(void);

int	zbx_pdc_get_mem_info(zbx_pdc_mem_info_t *info, char **error);
void	zbx_pdc_get_state_info(zbx_pdc_state_info_t *info);

/* discovery */

typedef struct zbx_pdc_discovery_data zbx_pdc_discovery_data_t;

zbx_pdc_discovery_data_t	*zbx_pdc_discovery_open(void);

void	zbx_pdc_discovery_close(zbx_pdc_discovery_data_t *data);

void	zbx_pdc_discovery_write_service(zbx_pdc_discovery_data_t *data, zbx_uint64_t druleid, zbx_uint64_t dcheckid,
		const char *ip, const char *dns, int port, int status, const char *value, int clock);

void	zbx_pdc_discovery_write_host(zbx_pdc_discovery_data_t *data, zbx_uint64_t druleid, const char *ip,
		const char *dns, int status, int clock);

int	zbx_pdc_discovery_get_rows(struct zbx_json *j, zbx_uint64_t *lastid, int *more);

void	zbx_pdc_discovery_set_lastid(const zbx_uint64_t lastid);

/* auto registration */

void	zbx_pdc_autoreg_write_host(const char *host, const char *ip, const char *dns, unsigned short port,
		unsigned int connection_type, const char *host_metadata, int flags, int clock);

int	zbx_pdc_autoreg_get_rows(struct zbx_json *j, zbx_uint64_t *lastid, int *more);

void	zbx_pdc_autoreg_set_lastid(const zbx_uint64_t lastid);


/* history */

typedef struct zbx_pdc_history_data zbx_pdc_history_data_t;

zbx_pdc_history_data_t	*zbx_pdc_history_open(void);

void	zbx_pdc_history_close(zbx_pdc_history_data_t *data);

void	zbx_pdc_history_write_value(zbx_pdc_history_data_t *data, zbx_uint64_t itemid, int state, const char *value,
		const zbx_timespec_t *ts, int flags, time_t now);

void	zbx_pdc_history_write_meta_value(zbx_pdc_history_data_t *data, zbx_uint64_t itemid, int state,
		const char *value, const zbx_timespec_t *ts, int flags, zbx_uint64_t lastlogsize, int mtime,
		int timestamp, int logeventid, int severity, const char *source, time_t now);

int	zbx_pdc_history_get_rows(struct zbx_json *j, zbx_uint64_t *lastid, int *more);

void	zbx_pdc_set_history_lastid(const zbx_uint64_t lastid);

#endif
