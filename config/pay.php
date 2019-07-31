<?php

return [
	'alipay' =>[
		'app_id'=>'2016100900646874',
		'ali_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwGqufMeQgVMYmt0yUZdKpIoFs6PnV1/APbo5cCmhIg+KF8F8je1yXj0h7rC3kcN5XLNeMIIlQz1XxddP10B0Zd1p1WqpCKk+XbaAk6rCb4JcfR/Cm8XBJfyCPJS0O1GXNHYjVgcvCvE7XT5O2+ipmClSxkz1H1j2UF8tr+Eg8yXMJlFgN3QAceuu+491ZhwViUdTS/lraTiZKUMfBVr9zwchZL6uUeq3DU8Mw7BIAIWiwSd1X6GXXoOuZDRj5EckncO4QIGtFmw6OjfHMlVlpad6kdIaTIktCU0omKQTuvj06sg0b8z3vd2jCYXczwcJuCd6QGBB+F3YKBhQ/vO6xQIDAQAB',
		'private_key' => 'MIIEowIBAAKCAQEA1p1ov6qIETMl2Qr9HMs5EY74qo1Ptrekb+au2HYfTGUjm5nnf+kYhv6eqfxKxURYeZ+kIVlrQ76506o4uEg/ArPou+9u5akQdiHUQ7zFDgR8bF5vMZPis3IDZ2Je7lN2VQCURLOz+SjqNQhbVkAoNlfcq7HXOxjeBxmGFxQgIwilgJzDsQlxIOgmSn/3/nXmsdyTKHsT0w9dZyjBqo783FMUk/2itHc4FrJr2cEaSJ67KDBasfrqUpeXAwkThzYxnqc41gfQXTnQwgLG/WhuAJLmhEH7WWUpSKt2XSisVIlm86j0I7M75J0Z8sVLZCuAtXwTZCOgtOnICKes/WwxpwIDAQABAoIBAFj0TaKD8HoQlTNtFpSEt1bLx84JXG0DmSi4mOgnrblggm3QUN/3oa7ygpk6dVNmjLV8se8QvSELQMK232OirltUbrbW+0Q92xDb3Ltm1taEjX3tdnE6NtDiBP8pCugFuBprbwk64vH3P3xTxmftl71Olaykve1E1WJj9exC7z8VZ+a5W7wpdiqSRvlGs03JOJlyrT3R90NHKGkMrPpsB6F75ZC4Y5zp8qsBRQE/c6RNdP3Blj0KD91L5+mTf4WmgDsK59sXOXWQwxmtqD5Muu+43k0ERurYITlB4KQ7Q1TjMDpli0g1CVu7a73t0vlbnP1HwHnLdlLTRJLbRWVEpyECgYEA9LL7UWcoCevoo3DuZEMpTRCLNzSZ7igkUCCECq2W6KfRpMMh9o6hcKDBY33IgzZ4JJSdYwRmIYupDRfoajzoXtsJ+9auUXzKPPdzbYlbNRBXT1JWpkT8CQxOOuaH0CwpGwoWgxJlcGDhUYX3XPFQ0I1zAz5hAjqHBCCzn9YSgpMCgYEA4Ia/nleNnlt7hQC4Uva0ZSF+iFE5f6N4FD94g83gRdZ0x5GL5w5MJsmN0G5TPi8NzyEaEsuMJKaNYd7OZWzHE6BDOnP0dmzWCAaQ/W97LDrzMYIwgcJHKQs3QOjZ3KV39a8894QgNGFYuGQejoSCfilsVx3E4TfCol+a26cMXR0CgYAs30165gHPn/UvU5xDwpUmTqxY2UqJA8906iGMm7yauXuWTTpDLnCoaLJN/ljwxuJNQvuBluLrr+K2RfYW1Uh/bIduKTYaN1oWyAHgyllxZ6e7IgxFvUzClAEch/3LzO1UygrKymrTqtBm1LxBzPbW57l5lMzRTc5IkX6fBvCqBQKBgQC4dpxpZ78nEjvp6syFBMDysVD2h9z4e4IFCJxnlTYjymyJJY/OVdXErCUB0BI97YSn3qdAN9C8r8VKWjYx5+uquSlila+LuCEj2Nk4DmYg0ZHJ5A8cHFRAaUGM54FOIPfsdntdU0TuI+gzHbZpGn7nTfr/m6qL6jbTFj7xJU4oFQKBgFvJo06auvKzih1ooo2/mj6jzwsmsOR9rrUDQjXmv1HBCbx0IYv7Yty4tiLCnSQySJizXxI+qqaHwj1xd2FI1iz/KLJ90N+TTMeuXVKKyh5UDoSjBHpEUmDH3OqccThiKOyrjrm5jeGb/euPXib8wfDjJz2TJp70dXt3BTjX9D+f',
		'log'=>[
			'file' => storage_path('logs/alipy.log')
		],
	],

	'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];